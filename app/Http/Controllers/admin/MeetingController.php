<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MeetingController extends Controller
{
    private function getDefaultAssigneeUser(Request $request): ?User
    {
        $branchId = $this->resolveBranchId($request);

        return User::whereIn('role', ['admin', 'sub-admin'])
            ->where('isDeleted', 0)
            ->where('id', $branchId)
            ->select('id', 'name', 'email', 'phone', 'role')
            ->first();
    }

    private function resolveAssignedTo(Request $request): ?int
    {
        $user = Auth::user();

        if ($user && $user->role === 'staff') {
            return (int) $user->id;
        }

        if ($request->filled('assigned_to')) {
            return (int) $request->input('assigned_to');
        }

        return $this->getDefaultAssigneeUser($request)?->id;
    }

    private function resolveBranchId(Request $request): int
    {
        $user = Auth::user();
        $selectedSubAdminId = $request->input('selectedSubAdminId')
            ?? $request->query('selectedSubAdminId')
            ?? session('selectedSubAdminId');

        if ($user->role === 'staff' && $user->branch_id) {
            return (int) $user->branch_id;
        }

        if ($user->role === 'admin') {
            return ! empty($selectedSubAdminId) ? (int) $selectedSubAdminId : (int) $user->id;
        }

        return (int) $user->id;
    }

    private function applyStaffVisibility($query)
    {
        $user = Auth::user();

        if ($user && $user->role === 'staff') {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('assigned_to', $user->id);
            });
        }

        return $query;
    }

    private function findVisibleMeetingOrFail(Request $request, int $id): Meeting
    {
        $query = Meeting::active()
            ->where('branch_id', $this->resolveBranchId($request));

        return $this->applyStaffVisibility($query)->findOrFail($id);
    }

    private function getMeetingQuery(Request $request)
    {
        $branchId = $this->resolveBranchId($request);
        $search = trim((string) $request->query('search', ''));

        $query = Meeting::with(['customer', 'assignedUser', 'branch'])
            ->active()
            ->where('branch_id', $branchId);

        $this->applyStaffVisibility($query);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('meeting_title', 'LIKE', "%{$search}%")
                    ->orWhere('meeting_type', 'LIKE', "%{$search}%")
                    ->orWhere('agenda', 'LIKE', "%{$search}%")
                    ->orWhere('address', 'LIKE', "%{$search}%")
                    ->orWhere('status', 'LIKE', "%{$search}%")
                    ->orWhereHas('customer', function ($subQ) use ($search) {
                        $subQ->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%")
                            ->orWhere('phone', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('assignedUser', function ($subQ) use ($search) {
                        $subQ->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%");
                    });
            });
        }

        return $query->orderByDesc('id');
    }

    public function meeting_list(Request $request)
    {
        return view('meeting.meetinglist');
    }

    public function add_meeting(Request $request)
    {
        return view('meeting.addmeeting');
    }

    public function edit_meeting(Request $request, $id)
    {
        return view('meeting.editmeeting', ['id' => $id]);
    }

    public function meeting_view($id)
    {
        return view('meeting.viewmeeting', ['id' => $id]);
    }

    public function meeting_report(Request $request)
    {
        return view('meeting.meetingreport');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id'  => 'required|exists:users,id',
            'assigned_to'  => 'nullable|exists:users,id',
            'meeting_title' => 'required|string|max:255',
            'meeting_type' => 'required|string|max:255',
            'agenda'       => 'required|string|max:1000',
            'address'      => 'nullable|string|max:500',
            'scheduled_on' => 'required|date',
            'status'       => 'required|in:Scheduled,Completed,Cancelled',
        ], [
            'customer_id.required'  => 'Customer is required.',
            'customer_id.exists'    => 'Selected customer does not exist.',
            'assigned_to.exists'    => 'Selected staff member does not exist.',
            'meeting_title.required' => 'Meeting title is required.',
            'meeting_type.required' => 'Meeting type is required.',
            'agenda.required'       => 'Agenda is required.',
            'scheduled_on.required' => 'Scheduled date & time is required.',
            'status.required'       => 'Status is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $scheduledOn = str_replace('T', ' ', $request->scheduled_on);
            if (strlen($scheduledOn) === 16) $scheduledOn .= ':00';

            $meeting = Meeting::create([
                'branch_id'     => $this->resolveBranchId($request),
                'customer_id'   => $request->customer_id,
                'assigned_to'   => $this->resolveAssignedTo($request),
                'created_by'    => Auth::id(),
                'meeting_title' => $request->meeting_title,
                'meeting_type'  => $request->meeting_type,
                'agenda'        => $request->agenda,
                'address'       => $request->address,
                'scheduled_on'  => $scheduledOn,
                'status'        => $request->status,
            ]);

            $customerName = User::where('id', $request->customer_id)->value('name') ?? 'Customer';
            $scheduledLabel = Carbon::parse($scheduledOn, 'Asia/Kolkata')->format('d-m-Y h:i A');

            Notification::create([
                'user_id'   => Auth::id(),
                'type'      => 'meeting',
                'title'     => 'New Meeting Created',
                'message'   => 'Meeting "' . ($meeting->meeting_title ?? 'N/A') . '" has been created successfully for ' . $customerName . ' on ' . $scheduledLabel . '.',
                'link'      => '/meeting-view/' . $meeting->id,
                'is_read'   => 0,
                'is_sound'  => 0,
                'branch_id' => $meeting->branch_id,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Meeting created successfully!',
                'data' => $meeting
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create meeting. Please try again.'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'customer_id'  => 'required|exists:users,id',
            'assigned_to'  => 'nullable|exists:users,id',
            'meeting_title' => 'required|string|max:255',
            'meeting_type' => 'required|string|max:255',
            'agenda'       => 'required|string|max:1000',
            'address'      => 'nullable|string|max:500',
            'scheduled_on' => 'required|date',
            'status'       => 'required|in:Scheduled,Completed,Cancelled',
        ], [
            'customer_id.required'  => 'Customer is required.',
            'customer_id.exists'    => 'Selected customer does not exist.',
            'assigned_to.exists'    => 'Selected staff member does not exist.',
            'meeting_title.required' => 'Meeting title is required.',
            'meeting_type.required' => 'Meeting type is required.',
            'agenda.required'       => 'Agenda is required.',
            'scheduled_on.required' => 'Scheduled date & time is required.',
            'status.required'       => 'Status is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $meeting = $this->findVisibleMeetingOrFail($request, (int) $id);

            $scheduledOn = str_replace('T', ' ', $request->scheduled_on);
            if (strlen($scheduledOn) === 16) $scheduledOn .= ':00';

            $meeting->update([
                'branch_id'     => $this->resolveBranchId($request),
                'customer_id'   => $request->customer_id,
                'assigned_to'   => $this->resolveAssignedTo($request),
                'meeting_title' => $request->meeting_title,
                'meeting_type'  => $request->meeting_type,
                'agenda'        => $request->agenda,
                'address'       => $request->address,
                'scheduled_on'  => $scheduledOn,
                'status'        => $request->status,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Meeting updated successfully!',
                'data' => $meeting
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update meeting. Please try again.'
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $meeting = Meeting::with(['customer', 'assignedUser', 'branch'])
                ->where('branch_id', $this->resolveBranchId($request));

            $meeting = $this->applyStaffVisibility($meeting)
                ->active()
                ->findOrFail($id);

            return response()->json([
                'status' => true,
                'data' => $meeting
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Meeting not found.'
            ], 404);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $meeting = $this->findVisibleMeetingOrFail($request, (int) $id);
            $meeting->update(['isDeleted' => 1]);

            return response()->json([
                'status' => true,
                'message' => 'Meeting deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete meeting. Please try again.'
            ], 500);
        }
    }

    public function getBranches(Request $request)
    {
        $branchId = $this->resolveBranchId($request);
        $search = $request->query('search', '');

        $branches = User::whereIn('role', ['admin', 'sub-admin'])
            ->where('isDeleted', 0)
            ->where('id', $branchId)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%");
                });
            })
            ->select('id', 'name', 'email', 'phone')
            ->orderBy('name')
            ->limit(50)
            ->get();

        return response()->json([
            'status' => true,
            'data' => $branches
        ]);
    }

    public function getCustomers(Request $request)
    {
        $branchId = $this->resolveBranchId($request);
        $search = $request->query('search', '');

        $customers = User::where('role', 'customer')
            ->where('isDeleted', 0)
            ->where('branch_id', $branchId)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%");
                });
            })
            ->select('id', 'name', 'email', 'phone')
            ->orderBy('name')
            ->limit(50)
            ->get();

        return response()->json([
            'status' => true,
            'data' => $customers
        ]);
    }

    public function getStaff(Request $request)
    {
        $branchId = $this->resolveBranchId($request);
        $search = $request->query('search', '');
        $defaultAssignee = $this->getDefaultAssigneeUser($request);

        $staff = User::where('role', 'staff')
            ->where('isDeleted', 0)
            ->where('branch_id', $branchId)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%");
                });
            })
            ->select('id', 'name', 'email', 'phone')
            ->orderBy('name')
            ->limit(50)
            ->get();

        return response()->json([
            'status' => true,
            'data' => $staff,
            'default_assignee' => $defaultAssignee
        ]);
    }
}
