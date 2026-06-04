<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveModel;
use App\Models\LeaveTypeModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class LeaveController extends Controller
{
    public function indexPage()
    {
        return view('leave.leaveview');
    }

    public function legacyPage()
    {
        return view('leave.leaveviews');
    }

    public function leaveRequest()
    {
        $authUser = auth()->user();
        $role = $this->normalizeRole((string) ($authUser->role ?? 'staff'));

        $query = LeaveModel::with(['user', 'leaveType'])
            ->orderBy('start_date', 'desc');

        if (!$this->isManagementRole($role)) {
            $query->where('user_id', $authUser->id);
        }

        $leaves = $query->get();

        return view('leave.leave-request', compact('leaves'));
    }

    public function createPage()
    {
        $authUser = auth()->user();
        $role = $this->normalizeRole((string) ($authUser->role ?? 'staff'));
        $nameColumn = $this->userDisplayColumn();

        $leaveTypes = LeaveTypeModel::query()
            ->orderBy('leave_type')
            ->get(['id', 'leave_type', 'allow_half_day']);

        $usersQuery = User::query()->select(['id', 'role']);

        if ($nameColumn === 'username') {
            $usersQuery->addSelect('username as display_name');
        } else {
            $usersQuery->addSelect('name as display_name');
        }

        $this->applyNotDeletedFilter($usersQuery);

        if ($this->isManagementRole($role)) {
            $usersQuery->whereRaw('LOWER(role) in (?, ?, ?)', ['hr', 'staff', 'employee']);
        } elseif ($authUser) {
            $usersQuery->where('id', (int) $authUser->id);
        }

        $users = $usersQuery
            ->orderBy('display_name')
            ->get()
            ->map(static function ($user) {
                return [
                    'id' => (int) $user->id,
                    'username' => (string) ($user->display_name ?? ''),
                    'role' => $user->role,
                ];
            })
            ->values()
            ->all();

        return view('leave.addleave', [
            'leaveTypes' => $leaveTypes,
            'users' => $users,
            'role' => $role,
            'currentUserId' => $authUser?->id,
        ]);
    }

    // Compatibility wrappers (old CI method names)
    public function view()
    {
        return $this->indexPage();
    }

    public function display()
    {
        return $this->createPage();
    }

    public function getAll(Request $request): JsonResponse
    {
        return $this->index($request);
    }

    public function create(Request $request): JsonResponse
    {
        return $this->store($request);
    }

    public function index(Request $request): JsonResponse
    {
        $authUser = $this->resolveAuthUser();
        if (!$authUser) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Token missing or invalid.',
            ], 401);
        }

        $role = $this->normalizeRole((string) $authUser->role);
        $requestedUserId = $request->filled('user_id') ? (int) $request->input('user_id') : null;
        $nameColumn = $this->userDisplayColumn();

        $usersQuery = User::query()->select(['id', 'profile_image', 'role']);
        if ($nameColumn === 'username') {
            $usersQuery->addSelect('username as firstname');
        } else {
            $usersQuery->addSelect('name as firstname');
        }

        $this->applyNotDeletedFilter($usersQuery);

        if ($this->isManagementRole($role)) {
            $usersQuery->whereRaw('LOWER(role) in (?, ?, ?)', ['hr', 'staff', 'employee']);
        } else {
            $usersQuery->where('id', (int) $authUser->id);
        }

        if ($requestedUserId) {
            if (!$this->isManagementRole($role) && (int) $authUser->id !== $requestedUserId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Forbidden: You can only view your own leave records.',
                ], 403);
            }
            $usersQuery->where('id', $requestedUserId);
        }

        $users = $usersQuery->orderBy('firstname')->get();
        $userIds = $users->pluck('id')->map(static fn ($id) => (int) $id)->all();

        if (empty($userIds)) {
            return response()->json([
                'status' => 'success',
                'data' => [],
                'role' => $role,
            ]);
        }

        $leaveQuery = LeaveModel::query()
            ->from('leaves')
            ->leftJoin('leave_type', 'leave_type.id', '=', 'leaves.leave_type_id')
            ->select([
                'leaves.id',
                'leaves.user_id',
                'leaves.leave_type_id as leave_id',
                'leaves.start_date',
                'leaves.end_date',
                'leaves.no_of_days as no_of_day',
                'leaves.reason',
                'leaves.status',
                'leave_type.leave_type',
            ])
            ->whereIn('leaves.user_id', $userIds)
            ->orderByDesc('leaves.start_date')
            ->orderByDesc('leaves.id');

        $leaveRows = $leaveQuery->get();

        $groupedLeaveRows = [];
        foreach ($leaveRows as $leaveRow) {
            $userId = (int) $leaveRow->user_id;
            $groupedLeaveRows[$userId][] = [
                'id'                 => (int) $leaveRow->id,
                'user_id'            => $userId,
                'leave_id'           => (int) ($leaveRow->leave_id ?? 0),
                'start_date'         => $leaveRow->start_date,
                'end_date'           => $leaveRow->end_date,
                'no_of_day'          => (int) ($leaveRow->no_of_day ?? 0),
                'reason'             => $leaveRow->reason,
                'status'             => $this->normalizeStatus((string) ($leaveRow->status ?? 'pending')),
                'leave_type'         => $leaveRow->leave_type,
                'created_by_username' => null,
            ];
        }

        $data = $users->map(static function ($user) use ($groupedLeaveRows) {
            $userId = (int) $user->id;
            return [
                'id' => $userId,
                'user_id' => $userId,
                'firstname' => $user->firstname,
                'profile_image' => $user->profile_image,
                'role' => $user->role,
                'leaves' => $groupedLeaveRows[$userId] ?? [],
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'role' => $role,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $authUser = $this->resolveAuthUser();
        if (!$authUser) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Token missing or invalid.',
            ], 401);
        }

        $payload = $request->isJson() ? $request->json()->all() : $request->all();

        $validator = Validator::make($payload, [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'leave_id' => ['required', 'integer', 'exists:leave_type,id'],
            'reason' => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'string', 'in:pending,approved,rejected,Pending,Approved,Rejected'],
            'half_day' => ['nullable', 'in:0,1,true,false'],
            'half_day_type' => ['nullable', 'string', 'in:first_half,second_half,First Half,Second Half'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $validated = $validator->validated();
        $targetUserId = (int) $validated['user_id'];
        $nameColumn = $this->userDisplayColumn();
        $targetUser = User::query()
            ->select(['id', 'role'])
            ->addSelect($nameColumn . ' as firstname')
            ->find($targetUserId);

        if (!$targetUser) {
            return response()->json([
                'status' => 'error',
                'message' => 'Selected user not found.',
            ], 404);
        }

        $role = $this->normalizeRole((string) $authUser->role);
        $targetRole = $this->normalizeRole((string) $targetUser->role);

        if ($this->isManagementRole($role)) {
            if ($role === 'hr' && !in_array($targetRole, ['hr', 'staff', 'employee'], true)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Forbidden: HR can only add leave for HR or staff users.',
                ], 403);
            }
        } elseif ((int) $authUser->id !== $targetUserId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden: You can only add leave for yourself.',
            ], 403);
        }

        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'])->startOfDay();
        $noOfDays = $startDate->diffInDays($endDate) + 1;
        $employeeFirstName = trim((string) ($targetUser->firstname ?? ''));

        $status = $this->normalizeStatus((string) ($validated['status'] ?? 'pending'));
        if (!$this->isManagementRole($role)) {
            $status = 'pending';
        }

        $leave = LeaveModel::create([
            'user_id'       => $targetUserId,
            'leave_id'      => (int) $validated['leave_id'], // for backwards compatibility
            'reason'        => trim((string) $validated['reason']),
            'start_date'    => $startDate->toDateString(),
            'end_date'      => $endDate->toDateString(),
            'no_of_day'     => $noOfDays, // for backwards compatibility
            'status'        => $status,
            'half_day'      => (int) ($validated['half_day'] ?? 0),
            'half_day_type' => $validated['half_day_type'] ?? null,
        ]);

        return response()->json([   
            'status' => 'success',
            'message' => 'Leave record added successfully',
            'id' => (int) $leave->id,
        ], 201);
    }

    public function updateStatus(Request $request, int $leaveId): JsonResponse|RedirectResponse
    {
        $authUser = auth()->user() ?? auth('api')->user();
        if (!$authUser) {
            return $this->statusUpdateResponse($request, [
                'status' => 'error',
                'message' => 'Unauthorized: Token missing or invalid.',
            ], 401);
        }

        $role = $this->normalizeRole((string) $authUser->role);
        if (!$this->isManagementRole($role)) {
            return $this->statusUpdateResponse($request, [
                'status' => 'error',
                'message' => 'Forbidden: Employees cannot update leave status.',
            ], 403);
        }

        $validator = Validator::make($request->isJson() ? $request->json()->all() : $request->all(), [
            'status' => ['required', 'string', 'in:pending,approve,approved,rejected,Pending,Approve,Approved,Rejected'],
        ]);

        if ($validator->fails()) {
            return $this->statusUpdateResponse($request, [
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $leave = LeaveModel::find($leaveId);
        if (!$leave) {
            return $this->statusUpdateResponse($request, [
                'status' => 'error',
                'message' => 'Leave request not found.',
            ], 404);
        }

        if ($role === 'hr') {
            $targetUser = User::query()->select(['id', 'role'])->find((int) $leave->user_id);
            $targetRole = $this->normalizeRole((string) ($targetUser->role ?? ''));

            if (!in_array($targetRole, ['staff', 'employee'], true)) {
                return $this->statusUpdateResponse($request, [
                    'status' => 'error',
                    'message' => 'Forbidden: HR can only update employee leave requests.',
                ], 403);
            }
        }

        $status = $this->normalizeStatus((string) $validator->validated()['status']);

        $leave->update([
            'status' => $status,
            'created_by' => (int) $authUser->id,
        ]);

        return $this->statusUpdateResponse($request, [
            'status' => 'success',
            'message' => 'Leave status updated successfully',
        ]);
    }

    public function updateStatusWeb(Request $request, int $leaveId)
    {
        $authUser = auth()->user();
        if (!$authUser) {
            abort(401);
        }

        $role = $this->normalizeRole((string) $authUser->role);
        if (!$this->isManagementRole($role)) {
            abort(403, 'Employees cannot update leave status.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,approve,approved,rejected,Pending,Approve,Approved,Rejected'],
        ]);

        $leave = LeaveModel::findOrFail($leaveId);

        if ($role === 'hr') {
            $targetUser = User::query()->select(['id', 'role'])->find((int) $leave->user_id);
            $targetRole = $this->normalizeRole((string) ($targetUser->role ?? ''));

            if (!in_array($targetRole, ['staff', 'employee'], true)) {
                abort(403, 'HR can only update employee leave requests.');
            }
        }

        $leave->update([
            'status' => $this->normalizeStatus((string) $validated['status']),
            'created_by' => (int) $authUser->id,
        ]);

        return redirect()
            ->route('leave.request')
            ->with('success', 'Leave status updated successfully.');
    }

    public function getEmployees(Request $request): JsonResponse
    {
        $response = $this->index($request);
        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        $payload = $response->getData(true);
        $employees = collect($payload['data'] ?? [])->map(static function (array $row) {
            return [
                'id' => $row['id'] ?? null,
                'firstname' => $row['firstname'] ?? null,
                'profile_image' => $row['profile_image'] ?? null,
                'role' => $row['role'] ?? null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $employees,
            'role' => $payload['role'] ?? null,
        ]);
    }

    /**
     * Resolve the authenticated user from API guard (Bearer token) or fall back to
     * the web session guard. This allows the leave API endpoints to work for users
     * who are logged in via the normal web session without needing a localStorage token.
     */
    private function resolveAuthUser(): ?User
    {
        $user = auth('api')->user();
        if ($user) {
            return $user;
        }
        return auth('web')->user();
    }

    private function normalizeRole(string $role): string
    {
        return strtolower(trim($role));
    }

    private function normalizeStatus(string $status): string
    {
        $normalized = strtolower(trim($status));
        if ($normalized === 'approve') {
            $normalized = 'approved';
        }

        return in_array($normalized, ['approved', 'rejected', 'pending'], true)
            ? $normalized
            : 'pending';
    }

    private function isManagementRole(string $role): bool
    {
        return in_array($role, ['admin', 'hr', 'sub-admin', 'subadmin'], true);
    }

    private function userDisplayColumn(): string
    {
        if (Schema::hasColumn('users', 'username')) {
            return 'username';
        }

        return 'name';
    }

    private function applyNotDeletedFilter(Builder $query): void
    {
        if (Schema::hasColumn('users', 'isDeleted')) {
            $query->where('isDeleted', 0);
            return;
        }

        if (Schema::hasColumn('users', 'is_deleted')) {
            $query->where('is_deleted', 0);
        }
    }

    private function statusUpdateResponse(Request $request, array $payload, int $statusCode = 200): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->isJson()) {
            return response()->json($payload, $statusCode);
        }

        $flashKey = ($payload['status'] ?? 'error') === 'success' ? 'success' : 'error';

        return redirect()
            ->route('leave.request')
            ->with($flashKey, $payload['message'] ?? 'Leave status update failed.');
    }
}
