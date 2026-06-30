<?php

namespace App\Http\Controllers\admin;

use App\Models\Module;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\UserPermission;
use App\Models\Setting;
use App\Services\StaffService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class StaffController extends Controller
{
    private function stateName($code)
    {
        $states = [
            '01' => 'Jammu and Kashmir',
            '02' => 'Himachal Pradesh',
            '03' => 'Punjab',
            '04' => 'Chandigarh',
            '05' => 'Uttarakhand',
            '06' => 'Haryana',
            '07' => 'Delhi',
            '08' => 'Rajasthan',
            '09' => 'Uttar Pradesh',
            '10' => 'Bihar',
            '11' => 'Sikkim',
            '12' => 'Arunachal Pradesh',
            '13' => 'Nagaland',
            '14' => 'Manipur',
            '15' => 'Mizoram',
            '16' => 'Tripura',
            '17' => 'Meghalaya',
            '18' => 'Assam',
            '19' => 'West Bengal',
            '20' => 'Jharkhand',
            '21' => 'Odisha',
            '22' => 'Chhattisgarh',
            '23' => 'Madhya Pradesh',
            '24' => 'Gujarat',
            '25' => 'Daman and Diu',
            '26' => 'Dadra and Nagar Haveli',
            '27' => 'Maharashtra',
            '28' => 'Andhra Pradesh',
            '29' => 'Karnataka',
            '30' => 'Goa',
            '31' => 'Lakshadweep',
            '32' => 'Kerala',
            '33' => 'Tamil Nadu',
            '34' => 'Puducherry',
            '35' => 'Andaman and Nicobar Islands',
            '36' => 'Telangana',
            '37' => 'Andhra Pradesh (New)',
        ];

        $normalised = str_pad(trim((string) $code), 2, '0', STR_PAD_LEFT);

        return $states[$normalised] ?? '';
    }

    private function resolveBranchId($authUser, Request $request)
    {
        if ($authUser->role === 'staff' && $authUser->branch_id) {
            return $authUser->branch_id;
        }

        if ($authUser->role === 'admin' && ! empty($request->selectedSubAdminId)) {
            return (int) $request->selectedSubAdminId;
        }

        return $authUser->id;
    }

    private function normalizeJoiningDate($value)
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        $formats = ['Y-m-d', 'd-M-Y', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'Y/m/d'];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (\Throwable $e) {
                // Try the next common format.
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return $value;
        }
    }

    public function staff_list(Request $request)
    {
        return view('staff/stafflist');
    }
    public function add_staff(Request $request)
    {
        $modules = Module::orderBy('id')->get();
        return view('staff/addstaff',compact('modules'));
    }
    public function edit_staff(Request $request)
    {
        return view('staff/editstaff');
    }
    public function staff_report(Request $request)
    {
        return view('staff/staffreport');
    }
    public function staff_view($id)
    {
        return view('staff.view_staff', ['id' => $id]);
    }

    public function staff_import(Request $request)
    {
        return view('staff.importstaff');
    }

    public function staff_import_sample()
    {
        $headers = [
            'name',
            'email',
            'password',
            'phone',
            'salary',
            'joining_date',
            'role',
            'country',
            'city',
            'state_code',
            'address',
        ];

        return response()->streamDownload(function () use ($headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fputcsv($handle, ['Kiran', 'kiran@gmail.com', 'password123', '9876543201', '25000', '2026-01-01', 'staff', 'India', 'Surat', '24', '123 Main Street']);
            fputcsv($handle, ['Preet', 'preet@gmail.com', 'password123', '9876543202', '30000', '2026-02-01', 'hr', 'India', 'Delhi', '07', '456 Park Avenue']);
            fputcsv($handle, ['Raveena', 'raveena@gmail.com', 'password123', '9876543203', '22000', '2026-03-01', 'staff', 'India', 'Bangalore', '29', '789 Lake Road']);
            fclose($handle);
        }, 'staff_import_sample.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function staff_export(Request $request)
    {
        $authUser = Auth::user();
        $branchId = $this->resolveBranchId($authUser, $request);

        $staff = User::with('details')
            ->whereIn('role', ['staff', 'hr'])
            ->where('branch_id', $branchId)
            ->where('isDeleted', 0)
            ->orderByDesc('id')
            ->get();

        $filename = 'staff_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response()->streamDownload(function () use ($staff) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'customer_name',
                'email',
                'password',
                'phone',
                'salary',
                'joining_date',
                'country',
                'city',
                'state_code',
                'state_name',
                'address',
                'role',
            ]);

            foreach ($staff as $person) {
                fputcsv($handle, [
                    $person->name,
                    $person->email,
                    '',
                    $person->phone,
                    optional($person->details)->salary ?? '',
                    optional($person->details)->joining_date ?? '',
                    optional($person->details)->country ?? '',
                    optional($person->details)->city ?? '',
                    $person->state_code,
                    $person->state_name,
                    optional($person->details)->address ?? '',
                    $person->role,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function staff_import_store(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $authUser = Auth::user();
        $branchId = $this->resolveBranchId($authUser, $request);
        $filePath = $request->file('csv_file')->getRealPath();
        $handle = fopen($filePath, 'r');

        if (! $handle) {
            return response()->json(['status' => false, 'message' => 'Unable to read the CSV file.'], 422);
        }

        $headers = fgetcsv($handle);
        if (! $headers) {
            fclose($handle);
            return response()->json(['status' => false, 'message' => 'CSV file is empty.'], 422);
        }

        $headers = array_map(fn ($header) => strtolower(trim((string) $header)), $headers);
        $imported = 0;
        $updated = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            $rowNumber = 1;

            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                if (count(array_filter($row, fn ($value) => trim((string) $value) !== '')) === 0) {
                    continue;
                }

                $data = array_combine($headers, array_pad($row, count($headers), ''));
                $name = trim((string) ($data['customer_name'] ?? $data['name'] ?? ''));
                $email = trim((string) ($data['email'] ?? ''));
                $password = trim((string) ($data['password'] ?? ''));
                $phone = trim((string) ($data['phone'] ?? ''));
                $joiningDate = $this->normalizeJoiningDate($data['joining_date'] ?? '');
                $role = strtolower(trim((string) ($data['role'] ?? 'staff')));

                if ($name === '' || $email === '' || $phone === '' || $joiningDate === '') {
                    $errors[] = "Row {$rowNumber}: name, email, phone and joining_date are required.";
                    continue;
                }

                if (! preg_match('/^[0-9]{10}$/', $phone)) {
                    $errors[] = "Row {$rowNumber}: phone must be 10 digits.";
                    continue;
                }

                if (! in_array($role, ['staff', 'hr'], true)) {
                    $role = 'staff';
                }

                $stateCode = trim((string) ($data['state_code'] ?? ''));
                if (str_contains($stateCode, ' - ')) {
                    $stateCode = trim(explode(' - ', $stateCode)[0]);
                }

                $staff = User::where('branch_id', $branchId)
                    ->whereIn('role', ['staff', 'hr'])
                    ->where('isDeleted', 0)
                    ->where(function ($query) use ($phone, $email) {
                        $query->where('phone', $phone)
                            ->orWhere('email', $email);
                    })
                    ->first();

                $payload = [
                    'name'       => $name,
                    'email'      => $email,
                    'phone'      => $phone,
                    'gst_number' => trim((string) ($data['gst_number'] ?? '')) ?: null,
                    'pan_number' => trim((string) ($data['pan_number'] ?? '')) ?: null,
                    'state_code' => $stateCode ?: null,
                    'state_name' => $stateCode ? $this->stateName($stateCode) : null,
                    'role'       => $role,
                    'branch_id'  => $branchId,
                    'created_by' => $authUser->id,
                    'haspermission' => 1,
                ];

                if ($staff) {
                    if ($password !== '') {
                        $payload['password'] = Hash::make($password);
                    }
                    $staff->update($payload);
                    $updated++;
                } else {
                    $payload['password'] = Hash::make($password !== '' ? $password : 'password123');
                    $staff = new User();
                    $staff->fill($payload);
                    $staff->save();
                    $imported++;
                }

                UserDetail::updateOrCreate(
                    ['user_id' => $staff->id],
                    [
                        'country' => trim((string) ($data['country'] ?? '')) ?: null,
                        'city' => trim((string) ($data['city'] ?? '')) ?: null,
                        'address' => trim((string) ($data['address'] ?? '')) ?: null,
                        'salary' => trim((string) ($data['salary'] ?? '')) !== '' ? (float) $data['salary'] : null,
                        'joining_date' => $joiningDate,
                    ]
                );

                $defaultModules = [26, 28];
                if ($role === 'hr') {
                    $defaultModules[] = 29;
                }

                UserPermission::where('user_id', $staff->id)
                    ->whereNotIn('module_id', $defaultModules)
                    ->delete();

                foreach ($defaultModules as $moduleId) {
                    UserPermission::updateOrCreate(
                        [
                            'user_id' => $staff->id,
                            'module_id' => $moduleId,
                        ],
                        [
                            'view' => 1,
                            'add' => 1,
                            'edit' => 1,
                            'delete' => 1,
                        ]
                    );
                }

                Notification::create([
                    'user_id'   => $authUser->id,
                    'type'      => 'staff',
                    'title'     => $staff->wasRecentlyCreated ? 'Staff Imported' : 'Staff Updated',
                    'message'   => $name . ' has been processed from import.',
                    'link'      => '/staff-view/' . $staff->id,
                    'is_read'   => 0,
                    'is_sound'  => 0,
                    'branch_id' => $branchId,
                ]);
            }

            fclose($handle);
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Import completed. {$imported} staff imported, {$updated} updated.",
                'errors' => $errors,
            ]);
        } catch (\Throwable $e) {
            fclose($handle);
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
