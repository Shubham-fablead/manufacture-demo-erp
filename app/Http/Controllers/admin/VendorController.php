<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class VendorController extends Controller
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

    public function vendor_list(Request $request)
    {
        return view('vendor/supplierlist');
    }
    public function add_vendor(Request $request)
    {
        return view('vendor/addsupplier');
    }
    public function edit_vendor(Request $request)
    {
        return view('vendor/editsupplier');
    }
    public function vendor_report(Request $request)
    {
        return view('vendor/supplierreport');
    }
    public function vendor_view($id)
    {
        return view('vendor.view_vendor', ['id' => $id]);
    }

    public function vendor_import(Request $request)
    {
        return view('vendor.importvendor');
    }

    public function vendor_import_sample()
    {
        $headers = [
            'name',
            'email',
            'phone',
            'country',
            'city',
            'state_code',
            'address',
            'gst_number',
            'pan_number',
        ];

        return response()->streamDownload(function () use ($headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fputcsv($handle, ['Default Vendor', 'vendor@example.com', '9876543210', 'India', 'Surat', '24', 'Vendor Address', '24ABCDE1234F1Z5', 'ABCDE1234F']);
            fclose($handle);
        }, 'vendor_import_sample.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function vendor_export(Request $request)
    {
        $authUser = Auth::user();
        $branchId = $this->resolveBranchId($authUser, $request);

        $vendors = User::with('details')
            ->where('role', 'vendor')
            ->where('branch_id', $branchId)
            ->where('isDeleted', 0)
            ->orderByDesc('id')
            ->get();

        $filename = 'vendors_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response()->streamDownload(function () use ($vendors) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'name',
                'email',
                'phone',
                'country',
                'city',
                'state_code',
                'state_name',
                'address',
                'gst_number',
                'pan_number',
            ]);

            foreach ($vendors as $vendor) {
                fputcsv($handle, [
                    $vendor->name,
                    $vendor->email,
                    $vendor->phone,
                    optional($vendor->details)->country ?? '',
                    optional($vendor->details)->city ?? '',
                    $vendor->state_code,
                    $vendor->state_name,
                    optional($vendor->details)->address ?? '',
                    $vendor->gst_number,
                    $vendor->pan_number,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function vendor_import_store(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $authUser = Auth::user();
        $branchId = $this->resolveBranchId($authUser, $request);
        $filePath = $request->file('csv_file')->getRealPath();
        $handle = fopen($filePath, 'r');

        if (! $handle) {
            return response()->json([
                'status' => false,
                'message' => 'Unable to read the CSV file.',
            ], 422);
        }

        $headers = fgetcsv($handle);
        if (! $headers) {
            fclose($handle);

            return response()->json([
                'status' => false,
                'message' => 'CSV file is empty.',
            ], 422);
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
                $name = trim((string) ($data['name'] ?? $data['vendor_name'] ?? ''));
                $phone = trim((string) ($data['phone'] ?? ''));
                $email = trim((string) ($data['email'] ?? ''));

                if ($name === '' || $phone === '') {
                    $errors[] = "Row {$rowNumber}: name and phone are required.";
                    continue;
                }

                if (! preg_match('/^[0-9]{10}$/', $phone)) {
                    $errors[] = "Row {$rowNumber}: phone must be 10 digits.";
                    continue;
                }

                $stateCode = trim((string) ($data['state_code'] ?? ''));
                if (str_contains($stateCode, ' - ')) {
                    $stateCode = trim(explode(' - ', $stateCode)[0]);
                }

                $vendor = User::where('branch_id', $branchId)
                    ->where('role', 'vendor')
                    ->where('isDeleted', 0)
                    ->where(function ($query) use ($phone, $email) {
                        $query->where('phone', $phone);
                        if ($email !== '') {
                            $query->orWhere('email', $email);
                        }
                    })
                    ->first();

                $vendorPayload = [
                    'name'       => $name,
                    'email'      => $email ?: null,
                    'phone'      => $phone,
                    'gst_number' => trim((string) ($data['gst_number'] ?? '')) ?: null,
                    'pan_number' => trim((string) ($data['pan_number'] ?? '')) ?: null,
                    'role'       => 'vendor',
                    'state_code' => $stateCode ?: null,
                    'state_name' => $stateCode ? $this->stateName($stateCode) : null,
                    'branch_id'  => $branchId,
                    'created_by' => $authUser->id,
                ];

                if ($vendor) {
                    $vendor->update($vendorPayload);
                    $updated++;
                } else {
                    $vendor = new User();
                    $vendor->fill($vendorPayload);
                    $vendor->save();
                    $imported++;
                }

                UserDetail::updateOrCreate(
                    ['user_id' => $vendor->id],
                    [
                        'country' => trim((string) ($data['country'] ?? '')) ?: null,
                        'city'    => trim((string) ($data['city'] ?? '')) ?: null,
                        'address' => trim((string) ($data['address'] ?? '')) ?: null,
                    ]
                );

                Notification::create([
                    'user_id'   => $authUser->id,
                    'type'      => 'vendor',
                    'title'     => $vendor->wasRecentlyCreated ? 'Vendor Imported' : 'Vendor Updated',
                    'message'   => $name . ' has been processed from import.',
                    'link'      => '/vendor-view/' . $vendor->id,
                    'is_read'   => 0,
                    'is_sound'  => 0,
                    'branch_id' => $branchId,
                ]);
            }

            fclose($handle);
            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => "Import completed. {$imported} vendors imported, {$updated} updated.",
                'errors'  => $errors,
            ]);
        } catch (\Throwable $e) {
            fclose($handle);
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
