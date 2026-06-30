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

class CustomerController extends Controller
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

    public function customer_list(Request $request)
    {
        return view('customer/customerlist');
    }
    public function add_customer(Request $request)
    {
        return view('customer/addcustomer');
    }
    public function edit_customer(Request $request)
    {
        return view('customer/editcustomer');
    }
    public function customer_report(Request $request)
    {
        return view('customer/customerreport');
    }
    public function customer_view($id)
    {
        return view('customer.view_customer', ['id' => $id]);
    }

    public function customer_import(Request $request)
    {
        return view('customer.importcustomer');
    }

    public function customer_import_sample()
    {
        $headers = [
            'customer_name',
            'email',
            'phone',
            'country',
            'city',
            'state_code',
            'address',
            'delivery_address',
            'gst_number',
            'pan_number',
        ];

        return response()->streamDownload(function () use ($headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fputcsv($handle, ['Radhika', 'radhika@gmail.com', '9876543210', 'India', 'Surat', '24', 'Street 1', 'Street 1, Near Market', '24ABCDE1234F1Z5', 'ABCDE1234F']);
            fclose($handle);
        }, 'customer_import_sample.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function customer_export(Request $request)
    {
        $authUser = Auth::user();
        $branchId = $this->resolveBranchId($authUser, $request);

        $customers = User::with('details')
            ->where('role', 'customer')
            ->where('branch_id', $branchId)
            ->where('isDeleted', 0)
            ->orderByDesc('id')
            ->get();

        $filename = 'customers_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response()->streamDownload(function () use ($customers) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'customer_name',
                'email',
                'phone',
                'country',
                'city',
                'state_code',
                'state_name',
                'address',
                'delivery_address',
                'gst_number',
                'pan_number',
            ]);

            foreach ($customers as $customer) {
                fputcsv($handle, [
                    $customer->name,
                    $customer->email,
                    $customer->phone,
                    optional($customer->details)->country ?? '',
                    optional($customer->details)->city ?? '',
                    $customer->state_code,
                    $customer->state_name,
                    optional($customer->details)->address ?? '',
                    optional($customer->details)->delivery_address ?? '',
                    $customer->gst_number,
                    $customer->pan_number,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function customer_import_store(Request $request)
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
                $customerName = trim((string) ($data['customer_name'] ?? $data['name'] ?? ''));
                $phone = trim((string) ($data['phone'] ?? ''));
                $email = trim((string) ($data['email'] ?? ''));

                if ($customerName === '' || $phone === '') {
                    $errors[] = "Row {$rowNumber}: customer_name and phone are required.";
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

                $customer = User::where('branch_id', $branchId)
                    ->where('role', 'customer')
                    ->where('isDeleted', 0)
                    ->where(function ($query) use ($phone, $email) {
                        $query->where('phone', $phone);
                        if ($email !== '') {
                            $query->orWhere('email', $email);
                        }
                    })
                    ->first();

                $customerPayload = [
                    'name'       => $customerName,
                    'email'      => $email ?: null,
                    'phone'      => $phone,
                    'gst_number' => trim((string) ($data['gst_number'] ?? '')) ?: null,
                    'pan_number' => trim((string) ($data['pan_number'] ?? '')) ?: null,
                    'role'       => 'customer',
                    'state_code' => $stateCode ?: null,
                    'state_name' => $stateCode ? $this->stateName($stateCode) : null,
                    'branch_id'  => $branchId,
                    'created_by' => $authUser->id,
                ];

                if ($customer) {
                    $customer->update($customerPayload);
                    $updated++;
                } else {
                    $customer = new User();
                    $customer->fill($customerPayload);
                    $customer->save();
                    $imported++;
                }

                UserDetail::updateOrCreate(
                    ['user_id' => $customer->id],
                    [
                        'country'           => trim((string) ($data['country'] ?? '')) ?: null,
                        'city'              => trim((string) ($data['city'] ?? '')) ?: null,
                        'address'           => trim((string) ($data['address'] ?? '')) ?: null,
                        'delivery_address'  => trim((string) ($data['delivery_address'] ?? '')) ?: null,
                    ]
                );

                Notification::create([
                    'user_id'   => $authUser->id,
                    'type'      => 'customer',
                    'title'     => $customer->wasRecentlyCreated ? 'Customer Imported' : 'Customer Updated',
                    'message'   => $customerName . ' has been processed from import.',
                    'link'      => '/customer-view/' . $customer->id,
                    'is_read'   => 0,
                    'is_sound'  => 0,
                    'branch_id' => $branchId,
                ]);
            }

            fclose($handle);
            DB::commit();

            return response()->json([
                'status'   => true,
                'message'  => "Import completed. {$imported} customers imported, {$updated} updated.",
                'errors'   => $errors,
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
