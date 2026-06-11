<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\UserPermission;
use App\Services\StaffService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{

    public function getAllStaff(Request $request)
    {
        $user = Auth::guard('api')->user();
        $userBranchId = !empty($request->selectedSubAdminId)
            ? $request->selectedSubAdminId
            : ($user->id ?? null);

        // Pagination & search parameters
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $search = $request->input('search');

        $query = User::select(
            'id',
            'name',
            'email',
            'phone',
            'profile_image',
            'role',
            'gst_number',
            'pan_number'
        )
            ->whereIn('role', ['staff', 'hr'])
            ->where('branch_id', $userBranchId)
            ->where('isDeleted', 0)
            ->with('details:user_id,country,city,department_id,designation_id,joining_date,shift_time,working_location,salary,face_photo');

        // Apply search filter on name, email, phone
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('gst_number', 'LIKE', "%{$search}%")
                    ->orWhere('pan_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('details', function ($d) use ($search) {
                        $d->where('country', 'LIKE', "%{$search}%")
                            ->orWhere('city', 'LIKE', "%{$search}%");
                    });
            });
        }

        $staff = $query->orderByDesc('id')->paginate($perPage, ['*'], 'page', $page);

        // Map to include details (country, city)
        $data = $staff->map(function ($customer) {
            $details = $customer->details;
            return [
                'id'                => $customer->id,
                'name'              => $customer->name,
                'role'              => $customer->role,
                'email'             => $customer->email,
                'phone'             => $customer->phone,
                'gst_number'        => $customer->gst_number,
                'pan_number'        => $customer->pan_number,
                'profile_image'     => $customer->profile_image,
                'profile_image_url' => $customer->profile_image_url,
                'country'           => $details->country ?? '',
                'city'              => $details->city ?? '',
                'department_id'     => $details->department_id ?? null,
                'designation_id'    => $details->designation_id ?? null,
                'joining_date'      => $details->joining_date ?? null,
                'shift_time'        => $details->shift_time ?? '',
                'working_location'  => $details->working_location ?? '',
                'salary'            => $details->salary ?? null,
                'face_photo'        => $details->face_photo ?? null,
            ];
        });

        return response()->json([
            'status' => true,
            'data'   => $data,
            'pagination' => [
                'current_page' => $staff->currentPage(),
                'last_page'    => $staff->lastPage(),
                'per_page'     => $staff->perPage(),
                'total'        => $staff->total(),
                'next_page_url' => $staff->nextPageUrl(),
                'prev_page_url' => $staff->previousPageUrl(),
            ]
        ], 200);
    }

    public function getCustomerById($id)
    {
        $customer = User::find($id);

        if (! $customer) {
            return response()->json([
                'status' => false,
                'error'  => 'Customer not found',
            ], 404);
        }

        return response()->json([
            'status'   => true,
            'customer' => $customer,
        ], 200);
    }

    // public function createStaff(Request $request)
    // {
    //     $user    = Auth::guard('api')->user();
    //     $userBranchId = ! empty($request->sub_admin_id)
    //         ? $request->sub_admin_id
    //         : ($user->id ?? null);

    //     $rules =  [
    //         'customer_name' => 'required|string|max:80',
    //         'email'         => [
    //             'required',
    //             'email',
    //             Rule::unique('users', 'email')->where(function ($query) use ($userBranchId) {
    //                 return $query->where('branch_id', $userBranchId)->where('isDeleted', 0);
    //             }),
    //         ],
    //         'role'          => 'required|in:staff,hr',
    //         'phone'         => [
    //             'required',
    //             'numeric',
    //             'digits:10',
    //             Rule::unique('users')->where(function ($query) use ($userBranchId) {
    //                 return $query->where('branch_id', $userBranchId)->where('isDeleted', 0);
    //             }),
    //         ],
    //         'gst_number'    => 'nullable|string|max:15',
    //         'pan_number'    => 'nullable|string|max:15',
    //         'country'       => 'nullable|string|max:100',
    //         'department_id' => 'nullable|integer|exists:department,id',
    //         'designation_id' => 'nullable|integer|exists:designation,id',
    //         'joining_date'  => 'nullable|date',
    //         'working_location' => 'nullable|string|max:100',
    //         'salary'        => 'nullable|numeric|min:0',
    //         'password' => [
    //             'required',
    //             'string',
    //             'min:8',
    //             'max:255',
    //             'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/'
    //         ],
    //         'city'          => 'nullable|string|max:100',
    //         'address'       => 'nullable|string|max:500',
    //         'avatar'        => [
    //             'nullable',
    //             'image',
    //             'mimes:jpeg,png,jpg,webp,gif',
    //             'max:2048',
    //             function ($attribute, $value, $fail) {
    //                 if ($value && !in_array($value->getClientOriginalExtension(), ['jpeg', 'png', 'jpg', 'webp', 'gif'])) {
    //                     $fail('The ' . $attribute . ' must be an image file (jpeg, png, jpg, webp, gif).');
    //                 }
    //             },
    //         ],
    //         'face_photo'    => [
    //             'nullable',
    //             'image',
    //             'mimes:jpeg,png,jpg,webp,gif',
    //             'max:2048',
    //             function ($attribute, $value, $fail) {
    //                 if ($value && !in_array($value->getClientOriginalExtension(), ['jpeg', 'png', 'jpg', 'webp', 'gif'])) {
    //                     $fail('The ' . $attribute . ' must be an image file (jpeg, png, jpg, webp, gif).');
    //                 }
    //             },
    //         ],
    //     ];

    //     $messages = [
    //         'password.regex' =>
    //         'Password must contain at least one uppercase letter, one lowercase letter, one number and one special character (@$!%*?&).',
    //         'password.min' => 'Password must be at least 8 characters.',
    //     ];

    //     $validator = Validator::make($request->all(), $rules, $messages);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'errors' => $validator->errors(),
    //         ], 422);
    //     }

    //     $plainPassword = $request->password;
    //     $createdStaff = null;

    //     DB::transaction(function () use ($request, $userBranchId, &$createdStaff) {
    //         $customer = new User();
    //         $customer->name          = $request->customer_name;
    //         $customer->email         = $request->email;
    //         $customer->phone         = $request->phone;
    //         $customer->gst_number    = $request->gst_number;
    //         $customer->pan_number    = $request->pan_number;
    //         $customer->haspermission = $request->permission_type;
    //         $customer->password      = Hash::make($request->password);
    //         $customer->branch_id     = $userBranchId;
    //         $customer->role          = $request->role;
    //         $customer->status        = 1;

    //         if ($request->hasFile('avatar')) {
    //             $customer->profile_image = $request->file('avatar')
    //                 ->store('staff', 'public');
    //         }

    //         $customer->save();
    //         $createdStaff = $customer;

    //         $facePhotoPath = null;
    //         if ($request->hasFile('face_photo')) {
    //             $facePhotoPath = $request->file('face_photo')->store('faces', 'public');
    //         }

    //         UserDetail::create([
    //             'user_id' => $customer->id,
    //             'country' => $request->country,
    //             'city'    => $request->city,
    //             'address' => $request->address,
    //             'department_id' => $request->filled('department_id') ? (int) $request->department_id : null,
    //             'designation_id' => $request->filled('designation_id') ? (int) $request->designation_id : null,
    //             'joining_date' => $request->joining_date,
    //             'working_location' => $request->working_location,
    //             'salary' => $request->salary,
    //             'face_photo' => $facePhotoPath,
    //         ]);

    //         if (is_array($request->modules)) {
    //             foreach ($request->modules as $moduleData) {
    //                 UserPermission::create([
    //                     'user_id'   => $customer->id,
    //                     'module_id' => $moduleData['module_id'],
    //                     'view'      => isset($moduleData['view']) ? 1 : 0,
    //                     'add'       => isset($moduleData['add']) ? 1 : 0,
    //                     'edit'      => isset($moduleData['edit']) ? 1 : 0,
    //                     'delete'    => isset($moduleData['delete']) ? 1 : 0,
    //                 ]);
    //             }
    //         }

    //         // 🔔 CREATE NOTIFICATION FOR STAFF CREATION
    //         Notification::create([
    //             'user_id'   => $customer->id, // admin/staff who created
    //             'type'      => 'staff',
    //             'title'     => 'New Staff Member Created',
    //             'message'   => $customer->name . ' has been added as staff successfully.',
    //             'link'      => '/staff-view/' . $customer->id,
    //             'is_read'   => 0,
    //             'is_sound'  => 0,
    //             'branch_id' => $userBranchId,
    //         ]);
    //     });

    //     $mailSent = false;
    //     if ($createdStaff) {
    //         $setting = Setting::where('branch_id', $createdStaff->branch_id)->first();
    //         $sendMailEnabled = is_null($setting?->send_mail) ? true : (bool) $setting->send_mail;

    //         if ($sendMailEnabled) {
    //             $mailSent = StaffService::sendStaffCreatedEmail($createdStaff, $plainPassword);
    //         } else {
    //             Log::info('Staff creation email skipped: send_mail is off for branch ' . $createdStaff->branch_id);
    //         }
    //     }

    //     return response()->json([
    //         'status'  => true,
    //         'message' => $mailSent
    //             ? 'Staff created successfully and email sent.'
    //             : 'Staff created successfully.',
    //         'mail_sent' => $mailSent,
    //     ], 201);
    // }

    public function createStaff(Request $request)
{
    $user    = Auth::guard('api')->user();
    $userBranchId = ! empty($request->sub_admin_id)
        ? $request->sub_admin_id
        : ($user->id ?? null);

    $rules =  [
        'customer_name' => 'required|string|max:80',
        'email'         => [
            'nullable',
            'email',
            Rule::unique('users', 'email')->where(function ($query) use ($userBranchId) {
                return $query->where('branch_id', $userBranchId)->where('isDeleted', 0)
                    ->whereIn('role', ['staff', 'hr']);
            }),
        ],
        'role'          => 'required|in:staff,hr',
        'phone'         => [
            'required',
            'numeric',
            'digits:10',
            Rule::unique('users')->where(function ($query) use ($userBranchId) {
                return $query->where('branch_id', $userBranchId)->where('isDeleted', 0)
                    ->whereIn('role', ['staff', 'hr']);
            }),
        ],
        'gst_number'    => 'nullable|string|max:15',
        'pan_number'    => 'nullable|string|max:15',
        'country'       => 'nullable|string|max:100',
        'department_id' => 'nullable|integer|exists:department,id',
        'designation_id' => 'nullable|integer|exists:designation,id',
        'joining_date'  => 'nullable|date',
        'shift_time'    => 'nullable|string|max:50',
        'working_location' => 'nullable|string|max:100',
        'salary'        => 'nullable|numeric|min:0',
        'password' => [
            'required',
            'string',
            'min:8',
            'max:255',
        ],
        'city'          => 'nullable|string|max:100',
        'address'       => 'nullable|string|max:500',
        'avatar'        => [
            'nullable',
            'image',
            'mimes:jpeg,png,jpg,webp,gif',
            'max:2048',
            function ($attribute, $value, $fail) {
                if ($value && !in_array($value->getClientOriginalExtension(), ['jpeg', 'png', 'jpg', 'webp', 'gif'])) {
                    $fail('The ' . $attribute . ' must be an image file (jpeg, png, jpg, webp, gif).');
                }
            },
        ],
        'face_photo'    => [
            'nullable',
            'image',
            'mimes:jpeg,png,jpg,webp,gif',
            'max:2048',
            function ($attribute, $value, $fail) {
                if ($value && !in_array($value->getClientOriginalExtension(), ['jpeg', 'png', 'jpg', 'webp', 'gif'])) {
                    $fail('The ' . $attribute . ' must be an image file (jpeg, png, jpg, webp, gif).');
                }
            },
        ],
    ];

    $messages = [
       'password.min' => 'Password must be at least 8 characters.',
    ];

    $validator = Validator::make($request->all(), $rules, $messages);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

    $plainPassword = $request->password;
    $createdStaff = null;

    DB::transaction(function () use ($request, $userBranchId, &$createdStaff) {
        $customer = new User();
        $customer->name          = $request->customer_name;
        $customer->email         = $request->email;
        $customer->phone         = $request->phone;
        $customer->gst_number    = $request->gst_number;
        $customer->pan_number    = $request->pan_number;
        $customer->haspermission = $request->permission_type;
        $customer->password      = Hash::make($request->password);
        $customer->branch_id     = $userBranchId;
        $customer->role          = $request->role;
        $customer->status        = 1;

        if ($request->hasFile('avatar')) {
            $customer->profile_image = $request->file('avatar')
                ->store('staff', 'public');
        }

        $customer->save();
        $createdStaff = $customer;

        $facePhotoPath = null;
        if ($request->hasFile('face_photo')) {
            $facePhotoPath = $request->file('face_photo')->store('faces', 'public');
        } elseif ($request->filled('captured_photo')) {
            $imageParts = explode(";base64,", $request->input('captured_photo'));
            if (count($imageParts) == 2) {
                $imageBase64 = base64_decode($imageParts[1]);
                $fileName = uniqid() . '.jpg';
                $filePath = 'faces/' . $fileName;
                \Illuminate\Support\Facades\Storage::disk('public')->put($filePath, $imageBase64);
                $facePhotoPath = $filePath;
            }
        }
        
        if ($request->filled('face_descriptor')) {
            $descriptor = json_decode($request->input('face_descriptor'), true);
            if (is_array($descriptor) || is_object($descriptor)) {
                // Assuming face_descriptor column on users table
                $customer->face_descriptor = $descriptor;
                $customer->save();
            }
        }

        UserDetail::create([
            'user_id' => $customer->id,
            'country' => $request->country,
            'city'    => $request->city,
            'address' => $request->address,
            'department_id' => $request->filled('department_id') ? (int) $request->department_id : null,
            'designation_id' => $request->filled('designation_id') ? (int) $request->designation_id : null,
            'joining_date' => $request->joining_date,
            'shift_time' => $request->shift_time,
            'working_location' => $request->working_location,
            'salary' => $request->salary,
            'face_photo' => $facePhotoPath,
        ]);

        // ✅ Default permissions for Leave (26) and Attendance (28)
        $defaultModules = [26, 28];
        if ($request->role === 'hr') {
            $defaultModules[] = 29; // HR gets Payroll too
        }

        foreach ($defaultModules as $moduleId) {
            UserPermission::create([
                'user_id'   => $customer->id,
                'module_id' => $moduleId,
                'view'      => 1,
                'add'       => 1,
                'edit'      => 1,
                'delete'    => 1,
            ]);
        }

        // ✅ Custom module permissions from request (skip defaults to avoid duplicates)
        if (is_array($request->modules)) {
            foreach ($request->modules as $moduleData) {
                // Skip if already added as default
                if (in_array($moduleData['module_id'], $defaultModules)) {
                    continue;
                }

                UserPermission::create([
                    'user_id'   => $customer->id,
                    'module_id' => $moduleData['module_id'],
                    'view'      => isset($moduleData['view']) ? 1 : 0,
                    'add'       => isset($moduleData['add']) ? 1 : 0,
                    'edit'      => isset($moduleData['edit']) ? 1 : 0,
                    'delete'    => isset($moduleData['delete']) ? 1 : 0,
                ]);
            }
        }

        // 🔔 CREATE NOTIFICATION FOR STAFF CREATION
        Notification::create([
            'user_id'   => $customer->id,
            'type'      => 'staff',
            'title'     => 'New Staff Member Created',
            'message'   => $customer->name . ' has been added as staff successfully.',
            'link'      => '/staff-view/' . $customer->id,
            'is_read'   => 0,
            'is_sound'  => 0,
            'branch_id' => $userBranchId,
        ]);
    });

    $mailSent = false;
    if ($createdStaff) {
        $setting = Setting::where('branch_id', $createdStaff->branch_id)->first();
        $sendMailEnabled = is_null($setting?->send_mail) ? true : (bool) $setting->send_mail;

        if ($sendMailEnabled) {
            $mailSent = StaffService::sendStaffCreatedEmail($createdStaff, $plainPassword);
        } else {
            Log::info('Staff creation email skipped: send_mail is off for branch ' . $createdStaff->branch_id);
        }
    }

    return response()->json([
        'status'  => true,
        'message' => $mailSent
            ? 'Staff created successfully and email sent.'
            : 'Staff created successfully.',
        'mail_sent' => $mailSent,
    ], 201);
}

    public function deleteStaff(Request $request, $id)
    {
        $customer = User::find($id);

        if (! $customer) {
            return response()->json([
                'status' => false,
                'error'  => 'Staff not found',
            ], 404);
        }

        if (Order::where('user_id', $id)->where('isDeleted', 0)->exists()) {
            return response()->json([
                'status' => false,
                'error'  => 'This Staff cannot be deleted because they have placed orders.',
            ], 400);
        }

        DB::transaction(function () use ($id, $customer) {
            UserDetail::where('user_id', $id)->update(['isDeleted' => 1]);
            $customer->update(['isDeleted' => 1]);
        });

        return response()->json([
            'status'  => true,
            'message' => 'Staff deleted successfully!',
        ]);
    }

    public function getStaff($id)
    {
        $customer = User::with('details')->find($id);
        if (! $customer) {
            return response()->json(['status' => false, 'error' => 'Staff not found'], 404);
        }
        // dd($customer);

        // Get all modules
        $modules = Module::all();

        // Fetch permissions for this user
        $permissions = UserPermission::where('user_id', $id)->get();

        return response()->json([
            'status'      => true,
            'customer'    => $customer,
            'modules'     => $modules,
            'permissions' => $permissions,
        ]);
    }

    // public function updateStaff(Request $request, $id)
    // {
    //     $userBranchId = User::where('id', $id)->value('branch_id');

    //     // Validate input data
    //     $validator = Validator::make($request->all(), [
    //         'staff_name' => 'required|string|max:80',
    //         'email'      => [
    //             'required',
    //             'email',
    //             Rule::unique('users', 'email')->ignore($id)->where(function ($query) use ($userBranchId) {
    //                 return $query->where('branch_id', $userBranchId)->where('isDeleted', 0);
    //             }),
    //         ],
    //         'phone'      => [
    //             'required',
    //             'numeric',
    //             'digits:10',
    //             Rule::unique('users')->ignore($id)->where(function ($query) use ($userBranchId) {
    //                 return $query->where('branch_id', $userBranchId)->where('isDeleted', 0);
    //             }),
    //         ],
    //         'country'    => 'nullable|string|max:100',
    //         'role'       => 'nullable|in:staff,hr',
    //         'city'       => 'nullable|string|max:100',
    //         'address'    => 'nullable|string|max:500',
    //         'department_id' => 'nullable|integer|exists:department,id',
    //         'designation_id' => 'nullable|integer|exists:designation,id',
    //         'joining_date' => 'nullable|date',
    //         'working_location' => 'nullable|string|max:100',
    //         'salary' => 'nullable|numeric|min:0',
    //         'password'   => 'nullable|string|min:8|max:255|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
    //         'avatar'        => [
    //             'nullable',
    //             'image',      // Ensures it's an image
    //             'mimes:jpeg,png,jpg,webp,gif', // Only image formats
    //             'max:2048',   // Max 2MB
    //             function ($attribute, $value, $fail) {
    //                 // Additional check to ensure it's not a PDF or other document
    //                 if ($value && !in_array($value->getClientOriginalExtension(), ['jpeg', 'png', 'jpg', 'webp', 'gif'])) {
    //                     $fail('The ' . $attribute . ' must be an image file (jpeg, png, jpg, webp, gif).');
    //                 }
    //             },
    //         ],
    //         'face_photo'    => [
    //             'nullable',
    //             'image',
    //             'mimes:jpeg,png,jpg,webp,gif',
    //             'max:2048',
    //             function ($attribute, $value, $fail) {
    //                 if ($value && !in_array($value->getClientOriginalExtension(), ['jpeg', 'png', 'jpg', 'webp', 'gif'])) {
    //                     $fail('The ' . $attribute . ' must be an image file (jpeg, png, jpg, webp, gif).');
    //                 }
    //             },
    //         ],
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'errors' => $validator->errors(),
    //         ], 422);
    //     }

    //     // Find the user
    //     $customer = User::find($id);
    //     if (! $customer) {
    //         return response()->json([
    //             'status' => false,
    //             'error'  => 'Staff not found',
    //         ], 404);
    //     }

    //     DB::transaction(function () use ($request, $customer, $id) {
    //         // Update main user fields
    //         $customer->name          = $request->staff_name;
    //         $customer->email         = $request->email;
    //         $customer->phone         = $request->phone;
    //         $customer->gst_number    = $request->gst_number;
    //         $customer->pan_number    = $request->pan_number;
    //         $customer->haspermission = $request->permission_type;
    //         if ($request->filled('role')) {
    //             $customer->role = $request->role;
    //         }

    //         if ($request->filled('password')) {
    //             $customer->password = Hash::make($request->password);
    //         }

    //         if ($request->hasFile('avatar')) {
    //             $path                    = $request->file('avatar')->store('staff', 'public');
    //             $customer->profile_image = $path;
    //         }

    //         $customer->save();

    //         // Update or create user details
    //         $customerDetail          = UserDetail::firstOrNew(['user_id' => $id]);
    //         $customerDetail->country = $request->country;
    //         $customerDetail->city    = $request->city;
    //         $customerDetail->address = $request->address;
    //         $customerDetail->department_id = $request->filled('department_id') ? (int) $request->department_id : null;
    //         $customerDetail->designation_id = $request->filled('designation_id') ? (int) $request->designation_id : null;
    //         $customerDetail->joining_date = $request->joining_date;
    //         $customerDetail->working_location = $request->working_location;
    //         $customerDetail->salary = $request->salary;

    //         if ($request->hasFile('face_photo')) {
    //             $customerDetail->face_photo = $request->file('face_photo')->store('faces', 'public');
    //         }

    //         $customerDetail->save();

    //         // Update permissions according to selected permission type
    //         if ((int) $request->input('permission_type', 0) === 0) {
    //             // Without permission: remove all permissions
    //             UserPermission::where('user_id', $customer->id)->delete();
    //         } else {
    //             $moduleInputs = $request->input('modules', []);

    //             // Upsert submitted permissions (keep module even if all 0)
    //             foreach ($moduleInputs as $moduleData) {

    //                 UserPermission::updateOrCreate(
    //                     [
    //                         'user_id'   => $customer->id,
    //                         'module_id' => (int) $moduleData['module_id'],
    //                     ],
    //                     [
    //                         'view'   => ! empty($moduleData['view']) ? (int) $moduleData['view'] : 0,
    //                         'add'    => ! empty($moduleData['add']) ? (int) $moduleData['add'] : 0,
    //                         'edit'   => ! empty($moduleData['edit']) ? (int) $moduleData['edit'] : 0,
    //                         'delete' => ! empty($moduleData['delete']) ? (int) $moduleData['delete'] : 0,
    //                     ]
    //                 );
    //             }
    //         }
    //         // dd($moduleData);
    //     });
    //     return response()->json([
    //         'status'        => true,
    //         'message'       => 'Staff updated successfully!',
    //         'data'          => [
    //             'user_id' => $customer->id,
    //         ],
    //         'staff'         => $customer,
    //         'staff_details' => $customer->details ?? null,
    //     ]);
    // }

    public function updateStaff(Request $request, $id)
    {
        $userBranchId = User::where('id', $id)->value('branch_id');

        // Validate input data
        $validator = Validator::make($request->all(), [
            'staff_name' => 'required|string|max:80',
            'email'      => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($id)->where(function ($query) use ($userBranchId) {
                    return $query->where('branch_id', $userBranchId)->where('isDeleted', 0)
                        ->whereIn('role', ['staff', 'hr']);
                }),
            ],
            'phone'      => [
                'required',
                'numeric',
                'digits:10',
                Rule::unique('users')->ignore($id)->where(function ($query) use ($userBranchId) {
                    return $query->where('branch_id', $userBranchId)->where('isDeleted', 0)
                        ->whereIn('role', ['staff', 'hr']);
                }),
            ],
            'country'    => 'nullable|string|max:100',
            'role'       => 'nullable|in:staff,hr',
            'city'       => 'nullable|string|max:100',
            'address'    => 'nullable|string|max:500',
            'department_id' => 'nullable|integer|exists:department,id',
            'designation_id' => 'nullable|integer|exists:designation,id',
            'joining_date' => 'nullable|date',
            'shift_time' => 'nullable|string|max:50',
            'working_location' => 'nullable|string|max:100',
            'salary' => 'nullable|numeric|min:0',
            'password'   => 'nullable|string|min:8|max:255',
            'avatar'        => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp,gif',
                'max:2048',
                function ($attribute, $value, $fail) {
                    if ($value && !in_array($value->getClientOriginalExtension(), ['jpeg', 'png', 'jpg', 'webp', 'gif'])) {
                        $fail('The ' . $attribute . ' must be an image file (jpeg, png, jpg, webp, gif).');
                    }
                },
            ],
            'face_photo'    => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp,gif',
                'max:2048',
                function ($attribute, $value, $fail) {
                    if ($value && !in_array($value->getClientOriginalExtension(), ['jpeg', 'png', 'jpg', 'webp', 'gif'])) {
                        $fail('The ' . $attribute . ' must be an image file (jpeg, png, jpg, webp, gif).');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Find the user
        $customer = User::find($id);
        if (! $customer) {
            return response()->json([
                'status' => false,
                'error'  => 'Staff not found',
            ], 404);
        }

        DB::transaction(function () use ($request, $customer, $id) {
            // Update main user fields
            $customer->name          = $request->staff_name;
            $customer->email         = $request->email;
            $customer->phone         = $request->phone;
            $customer->gst_number    = $request->gst_number;
            $customer->pan_number    = $request->pan_number;
            $customer->haspermission = $request->permission_type;
            if ($request->filled('role')) {
                $customer->role = $request->role;
            }

            if ($request->filled('password')) {
                $customer->password = Hash::make($request->password);
            }

            if ($request->hasFile('avatar')) {
                $path                    = $request->file('avatar')->store('staff', 'public');
                $customer->profile_image = $path;
            }

            $customer->save();

            // Update or create user details
            $customerDetail          = UserDetail::firstOrNew(['user_id' => $id]);
            $customerDetail->country = $request->country;
            $customerDetail->city    = $request->city;
            $customerDetail->address = $request->address;
            $customerDetail->department_id = $request->filled('department_id') ? (int) $request->department_id : null;
            $customerDetail->designation_id = $request->filled('designation_id') ? (int) $request->designation_id : null;
            $customerDetail->joining_date = $request->joining_date;
            $customerDetail->shift_time = $request->shift_time;
            $customerDetail->working_location = $request->working_location;
            $customerDetail->salary = $request->salary;

            if ($request->hasFile('face_photo')) {
                $customerDetail->face_photo = $request->file('face_photo')->store('faces', 'public');
            } elseif ($request->filled('captured_photo')) {
                $imageParts = explode(";base64,", $request->input('captured_photo'));
                if (count($imageParts) == 2) {
                    $imageBase64 = base64_decode($imageParts[1]);
                    $fileName = uniqid() . '.jpg';
                    $filePath = 'faces/' . $fileName;
                    \Illuminate\Support\Facades\Storage::disk('public')->put($filePath, $imageBase64);
                    $customerDetail->face_photo = $filePath;
                }
            }

            if ($request->filled('face_descriptor')) {
                $descriptor = json_decode($request->input('face_descriptor'), true);
                if (is_array($descriptor) || is_object($descriptor)) {
                    $customer->face_descriptor = $descriptor;
                    $customer->save();
                }
            }

            $customerDetail->save();

            // ✅ Default module IDs for Leave and Attendance
            $defaultModules = [26, 28];
           if ($request->role === 'hr') {
            $defaultModules[] = 29; // HR gets Payroll too
             }
            // Update permissions according to selected permission type
            if ((int) $request->input('permission_type', 0) === 0) {
                // Without permission: remove all permissions EXCEPT default modules (26, 28)
                UserPermission::where('user_id', $customer->id)
                    ->whereNotIn('module_id', $defaultModules)
                    ->delete();

                // ✅ Always ensure default permissions exist for Leave (26) and Attendance (28)
                foreach ($defaultModules as $moduleId) {
                    UserPermission::updateOrCreate(
                        [
                            'user_id'   => $customer->id,
                            'module_id' => $moduleId,
                        ],
                        [
                            'view'   => 1,
                            'add'    => 1,
                            'edit'   => 1,
                            'delete' => 1,
                        ]
                    );
                }
            } else {
                $moduleInputs = $request->input('modules', []);

                // ✅ Always ensure default permissions exist for Leave (26) and Attendance (28)
                foreach ($defaultModules as $moduleId) {
                    UserPermission::updateOrCreate(
                        [
                            'user_id'   => $customer->id,
                            'module_id' => $moduleId,
                        ],
                        [
                            'view'   => 1,
                            'add'    => 1,
                            'edit'   => 1,
                            'delete' => 1,
                        ]
                    );
                }

                // Upsert submitted permissions (skip default modules to avoid override)
                foreach ($moduleInputs as $moduleData) {
                    // Skip default modules — already handled above
                    if (in_array((int) $moduleData['module_id'], $defaultModules)) {
                        continue;
                    }

                    UserPermission::updateOrCreate(
                        [
                            'user_id'   => $customer->id,
                            'module_id' => (int) $moduleData['module_id'],
                        ],
                        [
                            'view'   => !empty($moduleData['view']) ? (int) $moduleData['view'] : 0,
                            'add'    => !empty($moduleData['add']) ? (int) $moduleData['add'] : 0,
                            'edit'   => !empty($moduleData['edit']) ? (int) $moduleData['edit'] : 0,
                            'delete' => !empty($moduleData['delete']) ? (int) $moduleData['delete'] : 0,
                        ]
                    );
                }
            }
        });

        return response()->json([
            'status'        => true,
            'message'       => 'Staff updated successfully!',
            'data'          => [
                'user_id' => $customer->id,
            ],
            'staff'         => $customer,
            'staff_details' => $customer->details ?? null,
        ]);
    }

    public function getStaffProfile($id)
    {
        $user = User::with([
            'userDetail.designation:id,designation_name',
            'userDetail.department:id,department_name',
        ])->find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $userDetail = $user->userDetail;

        $permissions = UserPermission::with('module')
            ->where('user_id', $id)
            ->get()
            ->map(function ($perm) {
                return [
                    'module' => $perm->module->module ?? 'Unknown',
                    'view'   => (bool) $perm->view,
                    'add'    => (bool) $perm->add,
                    'edit'   => (bool) $perm->edit,
                    'delete' => (bool) $perm->delete,
                ];
            });

        return response()->json([
            'name'          => $user->name,
            'email'         => $user->email,
            'phone'         => $user->phone,
            'gst_number'    => $user->gst_number,
            'role'          => $user->role,
            'profile_image' => $user->profile_image,
            'address'       => $userDetail->address ?? 'N/A',
            'city'          => $userDetail->city ?? 'N/A',
            'country'       => $userDetail->country ?? 'N/A',
            'designation_id'    => $userDetail->designation_id,
            'department_id'     => $userDetail->department_id,
            // 'joining_date'      => $userDetail->joining_date,
            'joining_date' => $userDetail->joining_date 
                ? \Carbon\Carbon::parse($userDetail->joining_date)->format('Y-m-d') 
                : null,
            'shift_time'        => $userDetail->shift_time,
            'working_location'  => $userDetail->working_location,
            'salary'            => $userDetail->salary,
            'face_photo'        => $userDetail->face_photo,
            'designation_name'  => $userDetail->designation->designation_name ?? null,
            'department_name'   => $userDetail->department->department_name ?? null,
            'permissions'   => $permissions,
        ]);
    }

    public function index()
    {
        $modules = Module::select('id', 'module as module_name', 'created_at')->get();

        return response()->json([
            'status'  => true,
            'message' => 'Modules fetched successfully',
            'data'    => $modules,
        ], 200);
    }
}
