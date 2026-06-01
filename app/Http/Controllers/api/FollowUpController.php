<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowUpController extends Controller
{
    private function getAuthUser()
    {
        return Auth::guard('api')->user() ?? Auth::user();
    }

    private function resolveBranchId(Request $request): int
    {
        $user = $this->getAuthUser();
        
        if (!$user) {
            return 0;
        }
        
        $selectedSubAdminId = $request->input('selectedSubAdminId')
            ?? $request->query('selectedSubAdminId')
            ?? session('selectedSubAdminId');

        if ($user->role === 'staff' && $user->branch_id) {
            return (int) $user->branch_id;
        }

        if ($user->role === 'admin' && ! empty($selectedSubAdminId)) {
            return (int) $selectedSubAdminId;
        }

        return (int) $user->id;
    }

    private function getAuthUserId(): ?int
    {
        return $this->getAuthUser()?->id;
    }

    private function applyStaffVisibility($query)
    {
        $user = $this->getAuthUser();

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

    public function index(Request $request)
    {
        $branchId = $this->resolveBranchId($request);
        $search   = trim((string) $request->query('search', ''));
        $perPage  = (int) $request->query('per_page', 10);
        $page     = (int) $request->query('page', 1);

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

        $paginated = $query->orderByDesc('id')->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status' => true,
            'data'   => $paginated->items(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'lead_id' => 'required_without:customer_id|nullable|exists:leads,id',
            'customer_id' => 'nullable|exists:users,id',
            'assigned_to' => 'nullable|exists:users,id',
            'purpose' => 'required|string|max:255',
            'comment' => 'nullable|string|max:1000',
            'priority' => 'required|in:Low,Medium,High',
            'status' => 'required|in:Pending,Rescheduled,Completed,Cancelled',
            'follow_up_datetime' => 'required|date|after:now',
        ], [
            'lead_id.required_without' => 'Lead is required.',
            'lead_id.exists' => 'Selected lead does not exist.',
            'customer_id.exists' => 'Selected customer does not exist.',
            'assigned_to.exists' => 'Selected staff member does not exist.',
            'purpose.required' => 'Purpose is required.',
            'priority.required' => 'Priority is required.',
            'status.required' => 'Status is required.',
            'follow_up_datetime.required' => 'Follow up date & time is required.',
            'follow_up_datetime.after' => 'Follow up date & time must be in future.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $followUp = FollowUp::create([
                'branch_id' => $this->resolveBranchId($request),
                'customer_id' => $request->customer_id,
                'lead_id' => $request->lead_id,
                'assigned_to' => $request->assigned_to,
                'created_by' => $this->getAuthUserId(),
                'purpose' => $request->purpose,
                'comment' => $request->comment,
                'priority' => $request->priority,
                'status' => $request->status,
                'follow_up_datetime' => $request->follow_up_datetime,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Follow up created successfully!',
                'data' => $followUp
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create follow up. Please try again.'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
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
            $followUp->update([
                'branch_id' => $this->resolveBranchId($request),
                'customer_id' => $request->customer_id,
                'lead_id' => $request->lead_id,
                'assigned_to' => $request->assigned_to,
                'purpose' => $request->purpose,
                'comment' => $request->comment,
                'priority' => $request->priority,
                'status' => $request->status,
                'follow_up_datetime' => $request->follow_up_datetime,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Follow up updated successfully!',
                'data' => $followUp
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update follow up. Please try again.'
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
            'data' => $staff
        ]);
    }
}
