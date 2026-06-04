<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GeneralSettingController extends Controller
{

    public function show(Request $request)
    {
        $user         = Auth::guard('api')->user();
        $role         = $user->role;
        $userBranchId = $user->id;
        $BranchId     = $user->branch_id;

        $selectedSubAdminId = $request->query('selectedSubAdminId');

        if ($role === 'admin' && ! empty($selectedSubAdminId)) {
            $subAdmin = User::where('id', $selectedSubAdminId)->first();
            // dd($subAdmin);
            if (! $subAdmin) {
                $selectedSubAdminId = $subAdmin->id;
            }
        } else {
            $selectedSubAdminId = $userBranchId;
        }
        // dd($selectedSubAdminId);
        // Common: fetch settings (filter by branch if needed)
        $settings = Setting::where('branch_id', $selectedSubAdminId)->first();

        return response()->json([
            'status'   => true,
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $user         = Auth::guard('api')->user();
        $role         = $user->role;
        $userBranchId = $user->id;

        $selectedSubAdminId = $request->selectedSubAdminId;

        if ($role === 'admin' && ! empty($selectedSubAdminId)) {
            $subAdmin = User::where('id', $selectedSubAdminId)->first();
            // dd($subAdmin);
            if (! $subAdmin) {
                $selectedSubAdminId = $subAdmin->id;
            }
        } else {
            $selectedSubAdminId = $userBranchId;
        }
        // dd($selectedSubAdminId);
        $request->validate([
            'shop_name'         => 'sometimes|required|string',
            'gst_num'           => 'nullable',
            'cin_no'            => 'nullable|string',
            'low_stock'         => 'nullable',
            'state_code'        => 'nullable',
            'email'             => 'sometimes|required|email',
            'phone'             => 'sometimes|required',
            'address'           => 'sometimes|required|string',
            'currency_symbol'   => 'sometimes|required|string',
            'currency_position' => 'sometimes|required|string',
            'logo'              => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'favicon'           => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'qr_code'           => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'bank_name' => 'sometimes|required|string',
            'branch' => 'sometimes|required|string',
            'ac_no' => 'sometimes|required|string',
            'ifsc_code' => 'sometimes|required|string',
            // ✅ New Rules validation
            'working_hours'     => 'nullable|numeric|min:0',
            'sunday_off'        => 'nullable|in:yes,no',
            'saturday_off'      => 'nullable|in:yes,no',
            'grace_period'      => 'nullable|numeric|min:0',
            'lunch_break'       => 'nullable|numeric|min:0',
            'open_time'         => 'nullable|date_format:H:i',
            'close_time'        => 'nullable|date_format:H:i',
            'invoice_size' => 'nullable|in:small,big',
            'send_mail'         => 'nullable|boolean',
            'tds_apply'         => 'nullable|boolean',
            'financial_year'    => 'nullable|boolean',
            // 📊 Dashboard Settings validation
            'crm_section_enabled' => 'nullable|in:Enable,Disable',
            'hr_section_enabled' => 'nullable|in:Enable,Disable',
            'erp_section_enabled' => 'nullable|in:Enable,Disable',
            'crm_lead_pipeline_box' => 'nullable|in:Enable,Disable',
            'crm_conversion_box' => 'nullable|in:Enable,Disable',
            'crm_followup_lead_box' => 'nullable|in:Enable,Disable',
            'crm_meeting_momentum_box' => 'nullable|in:Enable,Disable',
            'crm_lead_status_mix_chart' => 'nullable|in:Enable,Disable',
            'crm_activity_trend_chart' => 'nullable|in:Enable,Disable',
            'crm_pipeline_quality_table' => 'nullable|in:Enable,Disable',
            'crm_recent_leads_table' => 'nullable|in:Enable,Disable',
            'crm_next_7_days_table' => 'nullable|in:Enable,Disable',
            'hr_staff_strength_box' => 'nullable|in:Enable,Disable',
            'hr_active_staff_box' => 'nullable|in:Enable,Disable',
            'hr_monthly_attendance_box' => 'nullable|in:Enable,Disable',
            'hr_personal_progress_box' => 'nullable|in:Enable,Disable',
            'hr_7day_attendance_chart' => 'nullable|in:Enable,Disable',
            'hr_salary_payout_trend_chart' => 'nullable|in:Enable,Disable',
            'hr_payroll_snapshot_table' => 'nullable|in:Enable,Disable',
            'hr_attendance_watch_table' => 'nullable|in:Enable,Disable',
            'hr_payroll_status_table' => 'nullable|in:Enable,Disable',
            'erp_total_sales_box' => 'nullable|in:Enable,Disable',
            'erp_total_purchase_box' => 'nullable|in:Enable,Disable',
            'erp_total_expense_box' => 'nullable|in:Enable,Disable',
            'erp_sales_invoice_count_box' => 'nullable|in:Enable,Disable',
            'erp_purchase_invoice_count_box' => 'nullable|in:Enable,Disable',
            'erp_customers_count_box' => 'nullable|in:Enable,Disable',
            'erp_vendors_count_box' => 'nullable|in:Enable,Disable',
            'erp_sales_chart' => 'nullable|in:Enable,Disable',
            'erp_purchase_chart' => 'nullable|in:Enable,Disable',
            'erp_latest_sales_table' => 'nullable|in:Enable,Disable',
            'erp_latest_purchases_table' => 'nullable|in:Enable,Disable',
        ]);

        // Fetch or create branch-specific settings
        $settings = Setting::where('branch_id', $selectedSubAdminId)->first();

        if (! $settings) {
            $settings            = new Setting();
            $settings->branch_id = $selectedSubAdminId; // assign branch
        }

        $settings->gst_num           = $request->input('gst_num', $settings->gst_num);
        $settings->cin_no            = $request->input('cin_no', $settings->cin_no);
        $settings->low_stock         = $request->input('low_stock', $settings->low_stock);
        $settings->name              = $request->input('shop_name', $settings->name);
        $settings->email             = $request->input('email', $settings->email);
        $settings->phone             = $request->input('phone', $settings->phone);
        $settings->state_code        = $request->input('state_code', $settings->state_code);
        $settings->address           = $request->input('address', $settings->address);
        $settings->currency_position = $request->input('currency_position', $settings->currency_position);
        $settings->currency_symbol   = $request->input('currency_symbol', $settings->currency_symbol);
        $settings->bank_name = $request->input('bank_name', $settings->bank_name);
        $settings->branch = $request->input('branch', $settings->branch);
        $settings->ac_no = $request->input('ac_no', $settings->ac_no);
        $settings->ifsc_code = $request->input('ifsc_code', $settings->ifsc_code);
        // 🕒 Company Rules
        $settings->working_hours = $request->input('working_hours', $settings->working_hours);
        $settings->sunday_off    = $request->input('sunday_off', $settings->sunday_off);
        $settings->saturday_off  = $request->input('saturday_off', $settings->saturday_off);
        $settings->grace_period  = $request->input('grace_period', $settings->grace_period);
        $settings->lunch_break   = $request->input('lunch_break', $settings->lunch_break);
        $settings->open_time     = $request->input('open_time', $settings->open_time);
        $settings->close_time    = $request->input('close_time', $settings->close_time);
        $settings->invoice_size      = $request->input('invoice_size', $settings->invoice_size);
        $settings->send_mail     = $request->has('send_mail')
            ? (int) $request->send_mail
            : ($settings->send_mail ?? 1);
        $settings->tds_apply     = $request->has('tds_apply')
            ? (int) $request->tds_apply
            : ($settings->tds_apply ?? 0);
        $settings->financial_year = $request->has('financial_year')
            ? (int) $request->financial_year
            : ($settings->financial_year ?? 1);
        $settings->customer_whatsapp_message = $request->has('customer_whatsapp_message')
            ? (int) $request->customer_whatsapp_message
            : ($settings->customer_whatsapp_message ?? 0);
        $settings->admin_whatsapp_message = $request->has('admin_whatsapp_message')
            ? (int) $request->admin_whatsapp_message
            : ($settings->admin_whatsapp_message ?? 0);
        $settings->appointment_reminder_hours_before = $request->has('appointment_reminder_hours_before')
            ? (int) $request->appointment_reminder_hours_before
            : ($settings->appointment_reminder_hours_before ?? 3);

        // GPS / Location
        $settings->office_latitude  = $request->office_latitude  ?: $settings->office_latitude;
        $settings->office_longitude = $request->office_longitude ?: $settings->office_longitude;
        $settings->office_radius    = $request->has('office_radius') ? (int) $request->office_radius : ($settings->office_radius ?? 200);

        // 📊 Dashboard Settings
        $settings->crm_section_enabled = $request->input('crm_section_enabled', $settings->crm_section_enabled ?? 'Enable');
        $settings->hr_section_enabled = $request->input('hr_section_enabled', $settings->hr_section_enabled ?? 'Enable');
        $settings->erp_section_enabled = $request->input('erp_section_enabled', $settings->erp_section_enabled ?? 'Enable');

        // CRM Subsections
        $settings->crm_lead_pipeline_box = $request->input('crm_lead_pipeline_box', $settings->crm_lead_pipeline_box ?? 'Enable');
        $settings->crm_conversion_box = $request->input('crm_conversion_box', $settings->crm_conversion_box ?? 'Enable');
        $settings->crm_followup_lead_box = $request->input('crm_followup_lead_box', $settings->crm_followup_lead_box ?? 'Enable');
        $settings->crm_meeting_momentum_box = $request->input('crm_meeting_momentum_box', $settings->crm_meeting_momentum_box ?? 'Enable');
        $settings->crm_lead_status_mix_chart = $request->input('crm_lead_status_mix_chart', $settings->crm_lead_status_mix_chart ?? 'Enable');
        $settings->crm_activity_trend_chart = $request->input('crm_activity_trend_chart', $settings->crm_activity_trend_chart ?? 'Enable');
        $settings->crm_pipeline_quality_table = $request->input('crm_pipeline_quality_table', $settings->crm_pipeline_quality_table ?? 'Enable');
        $settings->crm_recent_leads_table = $request->input('crm_recent_leads_table', $settings->crm_recent_leads_table ?? 'Enable');
        $settings->crm_next_7_days_table = $request->input('crm_next_7_days_table', $settings->crm_next_7_days_table ?? 'Enable');

        // HR Subsections
        $settings->hr_staff_strength_box = $request->input('hr_staff_strength_box', $settings->hr_staff_strength_box ?? 'Enable');
        $settings->hr_active_staff_box = $request->input('hr_active_staff_box', $settings->hr_active_staff_box ?? 'Enable');
        $settings->hr_monthly_attendance_box = $request->input('hr_monthly_attendance_box', $settings->hr_monthly_attendance_box ?? 'Enable');
        $settings->hr_personal_progress_box = $request->input('hr_personal_progress_box', $settings->hr_personal_progress_box ?? 'Enable');
        $settings->hr_7day_attendance_chart = $request->input('hr_7day_attendance_chart', $settings->hr_7day_attendance_chart ?? 'Enable');
        $settings->hr_salary_payout_trend_chart = $request->input('hr_salary_payout_trend_chart', $settings->hr_salary_payout_trend_chart ?? 'Enable');
        $settings->hr_payroll_snapshot_table = $request->input('hr_payroll_snapshot_table', $settings->hr_payroll_snapshot_table ?? 'Enable');
        $settings->hr_attendance_watch_table = $request->input('hr_attendance_watch_table', $settings->hr_attendance_watch_table ?? 'Enable');
        $settings->hr_payroll_status_table = $request->input('hr_payroll_status_table', $settings->hr_payroll_status_table ?? 'Enable');

        // ERP Subsections
        $settings->erp_total_sales_box = $request->input('erp_total_sales_box', $settings->erp_total_sales_box ?? 'Enable');
        $settings->erp_total_purchase_box = $request->input('erp_total_purchase_box', $settings->erp_total_purchase_box ?? 'Enable');
        $settings->erp_total_expense_box = $request->input('erp_total_expense_box', $settings->erp_total_expense_box ?? 'Enable');
        $settings->erp_sales_invoice_count_box = $request->input('erp_sales_invoice_count_box', $settings->erp_sales_invoice_count_box ?? 'Enable');
        $settings->erp_purchase_invoice_count_box = $request->input('erp_purchase_invoice_count_box', $settings->erp_purchase_invoice_count_box ?? 'Enable');
        $settings->erp_customers_count_box = $request->input('erp_customers_count_box', $settings->erp_customers_count_box ?? 'Enable');
        $settings->erp_vendors_count_box = $request->input('erp_vendors_count_box', $settings->erp_vendors_count_box ?? 'Enable');
        $settings->erp_sales_chart = $request->input('erp_sales_chart', $settings->erp_sales_chart ?? 'Enable');
        $settings->erp_purchase_chart = $request->input('erp_purchase_chart', $settings->erp_purchase_chart ?? 'Enable');
        $settings->erp_latest_sales_table = $request->input('erp_latest_sales_table', $settings->erp_latest_sales_table ?? 'Enable');
        $settings->erp_latest_purchases_table = $request->input('erp_latest_purchases_table', $settings->erp_latest_purchases_table ?? 'Enable');

        if ($request->hasFile('logo')) {
            $logoPath       = $request->file('logo')->store('logos', 'public');
            $settings->logo = $logoPath;
        }

        if ($request->hasFile('favicon')) {
            $faviconPath       = $request->file('favicon')->store('favicons', 'public');
            $settings->favicon = $faviconPath;
        }

        if ($request->hasFile('qr_code')) {
            $qr_code           = $request->file('qr_code')->store('qr_codes', 'public');
            $settings->qr_code = $qr_code;
        }

        $settings->save();

        return response()->json([
            'status'   => true,
            'message'  => 'Settings updated successfully',
            'settings' => $settings,
        ]);
    }

    public function updateCompanyRules(Request $request)
    {
        $user               = Auth::guard('api')->user();
        $role               = $user->role;
        $userBranchId       = $user->id;
        $selectedSubAdminId = $request->selectedSubAdminId;

        // 🔹 Role-based branch selection
        if ($role === 'admin' && ! empty($selectedSubAdminId)) {
            $subAdmin = User::where('id', $selectedSubAdminId)->first();
            if ($subAdmin) {
                $selectedSubAdminId = $subAdmin->id;
            }
        } else {
            $selectedSubAdminId = $userBranchId;
        }

        // 🔹 Validation
        $validator = Validator::make($request->all(), [
            'working_hours' => 'required',
            'sunday_off'    => 'required|in:yes,no',
            'saturday_off'  => 'required|in:yes,no',
            'grace_period'  => 'required',
            'lunch_break'   => 'nullable',
            'open_time'     => 'required|date_format:H:i',
            'close_time'    => 'required|date_format:H:i',
            'overtime_after_hours' => 'nullable|numeric|min:0',
            'location_check_enabled' => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // 🔹 Fetch Setting for Branch
        $settings = Setting::where('branch_id', $selectedSubAdminId)->first();

        if (! $settings) {
            return response()->json([
                'status'  => false,
                'message' => 'Settings not found for this branch',
            ], 404);
        }

        // 🔹 Update Company Rules
        $settings->update([
            'working_hours' => $request->working_hours,
            'sunday_off'    => $request->sunday_off,
            'saturday_off'  => $request->saturday_off,
            'grace_period'  => $request->grace_period,
            'lunch_break'   => $request->lunch_break,
            'open_time'     => $request->open_time,
            'close_time'    => $request->close_time,
            'overtime_after_hours'   => $request->overtime_after_hours ?: null,
            'location_check_enabled' => (int) ($request->location_check_enabled ?? 0),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Company rules updated successfully',
            'data'    => $settings,
        ]);
    }
}
