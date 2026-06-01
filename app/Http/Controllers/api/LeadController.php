<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadStatusHistory;
use App\Models\User;
use App\Models\UserDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LeadController extends Controller
{
    private function getAuthUser()
    {
        return Auth::guard('api')->user() ?? Auth::user();
    }

    private function resolveBranchId(Request $request): int
    {
        $user = $this->getAuthUser();

        if (! $user) {
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

    private function getDefaultAssigneeUser(Request $request): ?User
    {
        $branchId = $this->resolveBranchId($request);

        return User::whereIn('role', ['admin', 'sub-admin'])
            ->where('isDeleted', 0)
            ->where('id', $branchId)
            ->select('id', 'name', 'email', 'phone', 'role')
            ->first();
    }

    private function branchScopedUserRule(int $branchId, array $roles)
    {
        return Rule::exists('users', 'id')->where(function ($query) use ($branchId, $roles) {
            $query->where('isDeleted', 0)
                ->where('branch_id', $branchId);

            if (! empty($roles)) {
                $query->whereIn('role', $roles);
            }
        });
    }

    private function findVisibleLeadOrFail(Request $request, int $id): Lead
    {
        $query = Lead::active()
            ->where('branch_id', $this->resolveBranchId($request));

        return $this->applyStaffVisibility($query)->findOrFail($id);
    }

    private function syncStatusHistory(Lead $lead, ?string $comment, bool $forceCreate = false): void
    {
        $user = $this->getAuthUser();
        if (! $user) {
            return;
        }

        if ($forceCreate || $lead->wasChanged('lead_status')) {
            LeadStatusHistory::create([
                'lead_id'    => $lead->id,
                'branch_id'  => $lead->branch_id,
                'status'     => $lead->lead_status,
                'comment'    => $comment,
                'updated_by' => $user->id,
            ]);
        }
    }

    public function index(Request $request)
    {
        $branchId = $this->resolveBranchId($request);
        $search   = trim((string) $request->query('search', ''));
        $perPage  = (int) $request->query('per_page', 10);
        $page     = (int) $request->query('page', 1);

        $query = Lead::with(['assignedUser', 'creator', 'statusHistories.updater'])
            ->active()
            ->where('branch_id', $branchId);

        $this->applyStaffVisibility($query);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('whatsapp', 'LIKE', "%{$search}%")
                    ->orWhere('company_name', 'LIKE', "%{$search}%")
                    ->orWhere('sic_code', 'LIKE', "%{$search}%")
                    ->orWhere('lead_source', 'LIKE', "%{$search}%")
                    ->orWhere('lead_status', 'LIKE', "%{$search}%")
                    ->orWhere('comment', 'LIKE', "%{$search}%")
                    ->orWhereHas('assignedUser', function ($subQ) use ($search) {
                        $subQ->where('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('creator', function ($subQ) use ($search) {
                        $subQ->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        $paginated = $query->orderByDesc('id')->paginate($perPage, ['*'], 'page', $page);
        $paginated->getCollection()->transform(function ($lead) {
            $lead->created_at_display = optional($lead->created_at)->format('d-M-Y');
            return $lead;
        });

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
        $branchId = $this->resolveBranchId($request);

        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'assigned_to'   => ['nullable', $this->branchScopedUserRule($branchId, ['staff', 'admin', 'sub-admin'])],
            'email'         => 'nullable|email|max:255',
            'phone'         => 'required|string|max:20',
            'whatsapp'      => 'nullable|string|max:20',
            'address'       => 'required|string|max:1000',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,gif,webp,bmp,svg,avif|max:2048',
            'company_name'  => 'nullable|string|max:255',
            'sic_code'      => 'nullable|string|max:100',
            'lead_source'   => 'required|string|max:255',
            'lead_status'   => 'required|in:New,Qualified,Working,Ready to Close,Closed Won,Closed Lost',
            'comment'       => 'nullable|string|max:2000',
        ], [
            'name.required'        => 'Lead name is required.',
            'phone.required'       => 'Phone is required.',
            'address.required'     => 'Address is required.',
            'lead_source.required'  => 'Lead source is required.',
            'lead_status.required'  => 'Lead status is required.',
            'assigned_to.exists'   => 'Selected staff member does not belong to this branch.',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $user = $this->getAuthUser();
            $imagePath = null;

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('leads', 'public');
            }

            $lead = Lead::create([
                'branch_id'    => $branchId,
                'assigned_to'  => $this->resolveAssignedTo($request),
                'created_by'   => $user?->id,
                'updated_by'   => $user?->id,
                'name'         => $request->name,
                'email'        => $request->email,
                'phone'        => $request->phone,
                'whatsapp'     => $request->whatsapp,
                'address'      => $request->address,
                'image'        => $imagePath,
                'company_name' => $request->company_name,
                'sic_code'     => $request->sic_code,
                'lead_source'  => $request->lead_source,
                'lead_status'  => $request->lead_status,
                'comment'      => $request->comment,
            ]);

            $this->syncStatusHistory($lead, $request->comment, true);

            return response()->json([
                'status'  => true,
                'message' => 'Lead created successfully!',
                'data'    => $lead,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to create lead. Please try again.',
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $lead = Lead::with(['assignedUser', 'creator', 'updater', 'statusHistories.updater'])
                ->where('branch_id', $this->resolveBranchId($request));

            $lead = $this->applyStaffVisibility($lead)
                ->active()
                ->findOrFail($id);

            return response()->json([
                'status' => true,
                'data'   => $lead,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Lead not found.',
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $branchId = $this->resolveBranchId($request);

        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'assigned_to'   => ['nullable', $this->branchScopedUserRule($branchId, ['staff', 'admin', 'sub-admin'])],
            'email'         => 'nullable|email|max:255',
            'phone'         => 'required|string|max:20',
            'whatsapp'      => 'nullable|string|max:20',
            'address'       => 'required|string|max:1000',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,gif,webp,bmp,svg,avif|max:2048',
            'company_name'  => 'nullable|string|max:255',
            'sic_code'      => 'nullable|string|max:100',
            'lead_source'   => 'required|string|max:255',
            'lead_status'   => 'required|in:New,Qualified,Working,Ready to Close,Closed Won,Closed Lost',
            'comment'       => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $lead = $this->findVisibleLeadOrFail($request, (int) $id);
            $previousStatus = $lead->lead_status;

            if ($request->hasFile('image')) {
                $lead->image = $request->file('image')->store('leads', 'public');
            }

            $lead->update([
                'branch_id'    => $branchId,
                'assigned_to'  => $this->resolveAssignedTo($request),
                'updated_by'   => $this->getAuthUser()?->id,
                'name'         => $request->name,
                'email'        => $request->email,
                'phone'        => $request->phone,
                'whatsapp'     => $request->whatsapp,
                'address'      => $request->address,
                'company_name' => $request->company_name,
                'sic_code'     => $request->sic_code,
                'lead_source'  => $request->lead_source,
                'lead_status'  => $request->lead_status,
                'comment'      => $request->comment,
            ]);

            if ($previousStatus !== $lead->lead_status) {
                $this->syncStatusHistory($lead, $request->comment, true);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Lead updated successfully!',
                'data'    => $lead,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to update lead. Please try again.',
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $lead = $this->findVisibleLeadOrFail($request, (int) $id);
            $lead->update([
                'isDeleted' => 1,
                'updated_by' => $this->getAuthUser()?->id,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Lead deleted successfully!',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to delete lead. Please try again.',
            ], 500);
        }
    }

    private function buildExportQuery(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $query = Lead::with(['assignedUser', 'creator'])
            ->active()
            ->where('branch_id', $this->resolveBranchId($request));

        $this->applyStaffVisibility($query);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('whatsapp', 'LIKE', "%{$search}%")
                    ->orWhere('company_name', 'LIKE', "%{$search}%")
                    ->orWhere('sic_code', 'LIKE', "%{$search}%")
                    ->orWhere('lead_source', 'LIKE', "%{$search}%")
                    ->orWhere('lead_status', 'LIKE', "%{$search}%")
                    ->orWhereHas('assignedUser', function ($subQ) use ($search) {
                        $subQ->where('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('creator', function ($subQ) use ($search) {
                        $subQ->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        return $query->orderByDesc('id')->get();
    }

    public function exportExcel(Request $request)
    {
        $leads = $this->buildExportQuery($request);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Leads');

        $sheet->setCellValue('A1', 'Manage Leads');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1:H1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:H1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = ['Sr.No', 'Lead Name', 'Lead Source', 'Assigned To', 'Created By', 'Created At', 'Status', 'Company Name'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '3', $header);
            $sheet->getStyle($col . '3')->getFont()->setBold(true);
            $sheet->getStyle($col . '3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F3F4F6');
            $sheet->getStyle($col . '3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $col++;
        }

        $row = 4;
        foreach ($leads as $index => $lead) {
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", (string) ($lead->name ?? ''));
            $sheet->setCellValue("C{$row}", (string) ($lead->lead_source ?? ''));
            $sheet->setCellValue("D{$row}", (string) ($lead->assignedUser->name ?? 'N/A'));
            $sheet->setCellValue("E{$row}", (string) ($lead->creator->name ?? 'N/A'));
            $sheet->setCellValue("F{$row}", optional($lead->created_at)->format('d-M-Y h:i A'));
            $sheet->setCellValue("G{$row}", (string) ($lead->lead_status ?? ''));
            $sheet->setCellValue("H{$row}", (string) ($lead->company_name ?? ''));
            $row++;
        }

        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $fileName     = 'Leads_' . now()->format('Ymd_His') . '.xlsx';
        $folder       = 'lead-exports';
        $relativePath = "{$folder}/{$fileName}";

        Storage::disk('public')->makeDirectory($folder);
        $writer = new Xlsx($spreadsheet);
        $writer->save(storage_path("app/public/{$relativePath}"));

        $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

        return response()->json([
            'status'    => true,
            'message'   => 'Lead Excel exported successfully.',
            'file_url'  => $fileUrl,
            'file_name' => $fileName,
        ]);
    }

    public function exportPdf(Request $request)
    {
        $branchId = $this->resolveBranchId($request);
        $leads = $this->buildExportQuery($request);
        $settings = DB::table('settings')
            ->where('branch_id', $branchId)
            ->first();

        $pdf = Pdf::loadView('lead.lead_pdf', compact('leads', 'settings'))
            ->setPaper('a4', 'landscape');

        $fileName     = 'Leads_' . now()->format('Ymd_His') . '.pdf';
        $folder       = 'lead-exports';
        $relativePath = "{$folder}/{$fileName}";

        Storage::disk('public')->makeDirectory($folder);
        Storage::disk('public')->put($relativePath, $pdf->output());

        $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

        return response()->json([
            'status'    => true,
            'message'   => 'Lead PDF generated successfully.',
            'file_url'  => $fileUrl,
            'file_name' => $fileName,
        ]);
    }

    public function convertToCustomer(Request $request, $id)
    {
        try {
            $lead = $this->findVisibleLeadOrFail($request, (int) $id);

            if (! empty($lead->converted_customer_id)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'This lead is already converted to a customer.',
                ], 409);
            }

            $branchId = $this->resolveBranchId($request);
            $user     = $this->getAuthUser();

            $validator = Validator::make([
                'name'  => $lead->name,
                'email' => $lead->email,
                'phone' => $lead->phone,
            ], [
                'name'  => 'required|string|max:255',
                'email' => [
                    'nullable',
                    'email',
                    Rule::unique('users', 'email')->where(function ($query) use ($branchId) {
                        return $query->where('branch_id', $branchId)
                            ->where('isDeleted', 0);
                    }),
                ],
                'phone' => [
                    'required',
                    'string',
                    'digits:10',
                    'regex:/^[0-9]{10}$/',
                    Rule::unique('users', 'phone')->where(function ($query) use ($branchId) {
                        return $query->where('branch_id', $branchId)
                            ->where('isDeleted', 0);
                    }),
                ],
            ], [
                'email.unique' => 'This email is already used by another customer or user.',
                'phone.unique' => 'This phone number is already used by another customer or user.',
                'phone.digits' => 'Customer phone number must be exactly 10 digits.',
                'phone.regex'  => 'Customer phone number must contain only numbers.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $customer = DB::transaction(function () use ($lead, $branchId, $user) {
                $password = Str::random(12);
                $profileImagePath = null;
                $stateCode = null;

                if (! empty($lead->state_code)) {
                    $stateCode = explode(' - ', (string) $lead->state_code)[0];
                }

                if ($lead->image && Storage::disk('public')->exists($lead->image)) {
                    $customerImageName = 'lead_customer_' . $lead->id . '_' . basename($lead->image);
                    $customerImagePath = 'customer/' . $customerImageName;
                    Storage::disk('public')->copy($lead->image, $customerImagePath);
                    $profileImagePath = $customerImagePath;
                }

                $customer = new User();
                $customer->branch_id = $branchId;
                $customer->name = $lead->name;
                $customer->email = $lead->email;
                $customer->phone = $lead->phone;
                $customer->gst_number = $lead->gst_number ?? null;
                $customer->pan_number = $lead->pan_number ?? null;
                $customer->state_code = $stateCode;
                $customer->password = $password;
                $customer->profile_image = $profileImagePath;
                $customer->role = 'customer';
                $customer->status = 1;
                $customer->isDeleted = 0;
                $customer->created_by = $user?->id;

                if (Schema::hasColumn('users', 'company_name')) {
                    $customer->company_name = $lead->company_name;
                }

                $customer->save();

                UserDetail::create([
                    'user_id' => $customer->id,
                    'address' => $lead->address,
                    'city'    => null,
                    'country' => null,
                ]);

                $lead->update([
                    'converted_customer_id' => $customer->id,
                    'lead_status'           => 'Closed Won',
                    'updated_by'            => $user?->id,
                ]);

                return $customer;
            });

            return response()->json([
                'status'  => true,
                'message' => 'Lead converted to customer successfully!',
                'data'    => [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Lead to customer conversion failed.', [
                'lead_id' => $id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to convert lead to customer. Please try again.',
            ], 500);
        }
    }

    public function getCustomers(Request $request)
    {
        $branchId = $this->resolveBranchId($request);

        $customers = User::where('role', 'customer')
            ->where('isDeleted', 0)
            ->where('branch_id', $branchId)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone']);

        return response()->json([
            'status' => true,
            'data'   => $customers,
        ]);
    }

    public function getStaff(Request $request)
    {
        $branchId = $this->resolveBranchId($request);
        $user = $this->getAuthUser();

        $staff = User::where('isDeleted', 0)
            ->where('branch_id', $branchId)
            ->whereIn('role', ['staff', 'admin', 'sub-admin'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        if ($user && $user->role === 'staff') {
            $staff = collect([(object) [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ]]);
        }

        return response()->json([
            'status' => true,
            'data'   => $staff->values(),
        ]);
    }

    private function resolveAssignedTo(Request $request): ?int
    {
        $user = $this->getAuthUser();

        if ($user && $user->role === 'staff') {
            return (int) $user->id;
        }

        if ($request->filled('assigned_to')) {
            return (int) $request->input('assigned_to');
        }

        return $this->getDefaultAssigneeUser($request)?->id ?? $user?->id;
    }
}
