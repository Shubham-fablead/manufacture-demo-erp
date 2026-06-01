<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FollowUpController extends Controller
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

    private function findVisibleFollowUpOrFail(Request $request, int $id): FollowUp
    {
        $query = FollowUp::active()
            ->where('branch_id', $this->resolveBranchId($request));

        return $this->applyStaffVisibility($query)->findOrFail($id);
    }

    private function getFollowUpQuery(Request $request)
    {
        $branchId = $this->resolveBranchId($request);
        $search = trim((string) $request->query('search', ''));

        $query = FollowUp::with(['customer', 'lead', 'assignedUser'])
            ->active()
            ->where('branch_id', $branchId);

        $this->applyStaffVisibility($query);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('purpose', 'LIKE', "%{$search}%")
                    ->orWhere('comment', 'LIKE', "%{$search}%")
                    ->orWhere('status', 'LIKE', "%{$search}%")
                    ->orWhere('priority', 'LIKE', "%{$search}%")
                    ->orWhereHas('lead', function ($subQ) use ($search) {
                        $subQ->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('company_name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%")
                            ->orWhere('phone', 'LIKE', "%{$search}%");
                    })
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

    public function follow_up_list(Request $request)
    {
        return view('followup.followuplist');
    }

    public function add_follow_up(Request $request)
    {
        return view('followup.addfollowup');
    }

    public function edit_follow_up(Request $request, $id)
    {
        return view('followup.editfollowup', ['id' => $id]);
    }

    public function follow_up_view($id)
    {
        return view('followup.view_followup', ['id' => $id]);
    }

    public function follow_up_report(Request $request)
    {
        return view('followup.followupreport');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_id' => 'required_without:customer_id|nullable|exists:leads,id',
            'customer_id' => 'nullable|exists:users,id',
            'assigned_to' => 'nullable|exists:users,id',
            'purpose' => 'required|string|max:255',
            'comment' => 'nullable|string|max:1000',
            'priority' => 'required|in:Low,Medium,High',
            'status' => 'required|in:Pending,Rescheduled,Completed,Cancelled',
            'follow_up_datetime' => 'required|date',
        ], [
            'lead_id.required_without' => 'Lead is required.',
            'lead_id.exists' => 'Selected lead does not exist.',
            'customer_id.exists' => 'Selected customer does not exist.',
            'assigned_to.exists' => 'Selected staff member does not exist.',
            'purpose.required' => 'Purpose is required.',
            'priority.required' => 'Priority is required.',
            'status.required' => 'Status is required.',
            'follow_up_datetime.required' => 'Follow up date & time is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Normalize datetime-local format (e.g. "2026-05-15T15:00") to MySQL format
            $followUpDatetime = str_replace('T', ' ', $request->follow_up_datetime);
            if (strlen($followUpDatetime) === 16) {
                $followUpDatetime .= ':00';
            }

            $followUp = FollowUp::create([
                'branch_id'          => $this->resolveBranchId($request),
                'customer_id'        => $request->customer_id,
                'lead_id'            => $request->lead_id,
                'assigned_to'        => $this->resolveAssignedTo($request),
                'created_by'         => Auth::id(),
                'purpose'            => $request->purpose,
                'comment'            => $request->comment,
                'priority'           => $request->priority,
                'status'             => $request->status,
                'follow_up_datetime' => $followUpDatetime,
            ]);

            $customerName = Lead::where('id', $request->lead_id)->value('name')
                ?? User::where('id', $request->customer_id)->value('name')
                ?? 'Lead';
            $followUpLabel = Carbon::parse($followUpDatetime, 'Asia/Kolkata')->format('d-m-Y h:i A');

            Notification::create([
                'user_id'   => Auth::id(),
                'type'      => 'followup',
                'title'     => 'New Follow Up Created',
                'message'   => 'Follow up "' . ($followUp->purpose ?? 'N/A') . '" has been created successfully for ' . $customerName . ' on ' . $followUpLabel . '.',
                'link'      => '/follow-up-view/' . $followUp->id,
                'is_read'   => 0,
                'is_sound'  => 0,
                'branch_id' => $followUp->branch_id,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Follow up created successfully!',
                'data' => $followUp
            ]);
        } catch (\Exception $e) {
            Log::error('FollowUp store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'input' => $request->except(['_token']),
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to create follow up. Please try again.',
                'debug'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'lead_id' => 'required_without:customer_id|nullable|exists:leads,id',
            'customer_id' => 'nullable|exists:users,id',
            'assigned_to' => 'nullable|exists:users,id',
            'purpose' => 'required|string|max:255',
            'comment' => 'nullable|string|max:1000',
            'priority' => 'required|in:Low,Medium,High',
            'status' => 'required|in:Pending,Rescheduled,Completed,Cancelled',
            'follow_up_datetime' => 'required|date',
        ], [
            'lead_id.required_without' => 'Lead is required.',
            'lead_id.exists' => 'Selected lead does not exist.',
            'customer_id.exists' => 'Selected customer does not exist.',
            'assigned_to.exists' => 'Selected staff member does not exist.',
            'purpose.required' => 'Purpose is required.',
            'priority.required' => 'Priority is required.',
            'status.required' => 'Status is required.',
            'follow_up_datetime.required' => 'Follow up date & time is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $followUp = $this->findVisibleFollowUpOrFail($request, (int) $id);

            // Normalize datetime-local format (e.g. "2026-05-15T15:00") to MySQL format
            $followUpDatetime = str_replace('T', ' ', $request->follow_up_datetime);
            if (strlen($followUpDatetime) === 16) {
                $followUpDatetime .= ':00';
            }

            $followUp->update([
                'branch_id'          => $this->resolveBranchId($request),
                'customer_id'        => $request->customer_id,
                'lead_id'            => $request->lead_id,
                'assigned_to'        => $this->resolveAssignedTo($request),
                'purpose'            => $request->purpose,
                'comment'            => $request->comment,
                'priority'           => $request->priority,
                'status'             => $request->status,
                'follow_up_datetime' => $followUpDatetime,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Follow up updated successfully!',
                'data' => $followUp
            ]);
        } catch (\Exception $e) {
            Log::error('FollowUp update error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'id'    => $id,
                'input' => $request->except(['_token']),
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to update follow up. Please try again.',
                'debug'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $followUp = FollowUp::with(['customer', 'lead', 'assignedUser'])
                ->where('branch_id', $this->resolveBranchId($request));

            $followUp = $this->applyStaffVisibility($followUp)
                ->active()
                ->findOrFail($id);

            return response()->json([
                'status' => true,
                'data' => $followUp
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Follow up not found.'
            ], 404);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $followUp = $this->findVisibleFollowUpOrFail($request, (int) $id);
            $followUp->update(['isDeleted' => 1]);

            return response()->json([
                'status' => true,
                'message' => 'Follow up deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete follow up. Please try again.'
            ], 500);
        }
    }

    public function getCustomers(Request $request)
    {
        $branchId = $this->resolveBranchId($request);
        $search = $request->query('search', '');

        $customers = Lead::active()
            ->where('branch_id', $branchId)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('company_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%");
                });
            })
            ->select('id', 'name', 'company_name', 'email', 'phone')
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
