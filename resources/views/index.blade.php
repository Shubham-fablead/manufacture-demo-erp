@extends('layout.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $role = auth()->user()->role ?? '';
        $user = auth()->user();
        $dashboardBranchId = session('selectedSubAdminId') ?: ($user->role === 'staff' && $user->branch_id ? $user->branch_id : $user->id);
        $today = \Carbon\Carbon::today('Asia/Kolkata');
        $now = \Carbon\Carbon::now('Asia/Kolkata');

        // Fetch dashboard visibility settings
        $settings = \App\Models\Setting::where('branch_id', $dashboardBranchId)->first();

        // Set default values if settings don't exist
        $dashboardSettings = [
            'crm_section_enabled' => $settings?->crm_section_enabled ?? 'Enable',
            'hr_section_enabled' => $settings?->hr_section_enabled ?? 'Enable',
            'erp_section_enabled' => $settings?->erp_section_enabled ?? 'Enable',
            'crm_lead_pipeline_box' => $settings?->crm_lead_pipeline_box ?? 'Enable',
            'crm_conversion_box' => $settings?->crm_conversion_box ?? 'Enable',
            'crm_followup_lead_box' => $settings?->crm_followup_lead_box ?? 'Enable',
            'crm_meeting_momentum_box' => $settings?->crm_meeting_momentum_box ?? 'Enable',
            'crm_lead_status_mix_chart' => $settings?->crm_lead_status_mix_chart ?? 'Enable',
            'crm_activity_trend_chart' => $settings?->crm_activity_trend_chart ?? 'Enable',
            'crm_pipeline_quality_table' => $settings?->crm_pipeline_quality_table ?? 'Enable',
            'crm_recent_leads_table' => $settings?->crm_recent_leads_table ?? 'Enable',
            'crm_next_7_days_table' => $settings?->crm_next_7_days_table ?? 'Enable',
            'hr_staff_strength_box' => $settings?->hr_staff_strength_box ?? 'Enable',
            'hr_active_staff_box' => $settings?->hr_active_staff_box ?? 'Enable',
            'hr_monthly_attendance_box' => $settings?->hr_monthly_attendance_box ?? 'Enable',
            'hr_personal_progress_box' => $settings?->hr_personal_progress_box ?? 'Enable',
            'hr_7day_attendance_chart' => $settings?->hr_7day_attendance_chart ?? 'Enable',
            'hr_salary_payout_trend_chart' => $settings?->hr_salary_payout_trend_chart ?? 'Enable',
            'hr_payroll_snapshot_table' => $settings?->hr_payroll_snapshot_table ?? 'Enable',
            'hr_attendance_watch_table' => $settings?->hr_attendance_watch_table ?? 'Enable',
            'hr_payroll_status_table' => $settings?->hr_payroll_status_table ?? 'Enable',
            'erp_total_sales_box' => $settings?->erp_total_sales_box ?? 'Enable',
            'erp_total_purchase_box' => $settings?->erp_total_purchase_box ?? 'Enable',
            'erp_total_expense_box' => $settings?->erp_total_expense_box ?? 'Enable',
            'erp_sales_invoice_count_box' => $settings?->erp_sales_invoice_count_box ?? 'Enable',
            'erp_purchase_invoice_count_box' => $settings?->erp_purchase_invoice_count_box ?? 'Enable',
            'erp_customers_count_box' => $settings?->erp_customers_count_box ?? 'Enable',
            'erp_vendors_count_box' => $settings?->erp_vendors_count_box ?? 'Enable',
            'erp_sales_chart' => $settings?->erp_sales_chart ?? 'Enable',
            'erp_purchase_chart' => $settings?->erp_purchase_chart ?? 'Enable',
            'erp_latest_sales_table' => $settings?->erp_latest_sales_table ?? 'Enable',
            'erp_latest_purchases_table' => $settings?->erp_latest_purchases_table ?? 'Enable',
        ];

        $crmRoles = ['admin', 'sub-admin', 'sales-manager'];
        $hrRoles = ['admin', 'sub-admin'];

        $leadQuery = \App\Models\Lead::active()->where('branch_id', $dashboardBranchId);
        $leadTotal = (clone $leadQuery)->count();
        $newLeadsThisMonth = (clone $leadQuery)->whereMonth('created_at', $today->month)->whereYear('created_at', $today->year)->count();
        $convertedLeads = (clone $leadQuery)->where(function ($query) {
            $query->where('lead_status', 'Closed Won')->orWhereNotNull('converted_customer_id');
        })->count();
        $conversionRate = $leadTotal > 0 ? round(($convertedLeads / $leadTotal) * 100) : 0;

        $pendingFollowUpsQuery = \App\Models\FollowUp::active()->where('branch_id', $dashboardBranchId)->whereIn('status', ['Pending', 'Rescheduled']);
        $followUpLeadCount = (clone $pendingFollowUpsQuery)->whereNotNull('lead_id')->count();
        $overdueFollowUps = (clone $pendingFollowUpsQuery)->where('follow_up_datetime', '<', $now)->count();
        $completedMeetingsThisMonth = \App\Models\Meeting::active()->where('branch_id', $dashboardBranchId)->where('status', \App\Models\Meeting::STATUS_COMPLETED)->whereMonth('scheduled_on', $today->month)->whereYear('scheduled_on', $today->year)->count();

        $leadStatusMix = (clone $leadQuery)->selectRaw('COALESCE(NULLIF(lead_status, ""), "Unassigned") as status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $recentLeads = (clone $leadQuery)->with('assignedUser')->latest()->take(4)->get();
        $crmNextItems = collect()
            ->merge(\App\Models\FollowUp::active()->with(['lead', 'customer', 'assignedUser'])->where('branch_id', $dashboardBranchId)->whereBetween('follow_up_datetime', [$today, $today->copy()->addDays(7)->endOfDay()])->orderBy('follow_up_datetime')->take(4)->get()->map(fn ($item) => [
                'work' => 'Follow-up',
                'party' => $item->subject_name,
                'owner' => $item->assignedUser?->name ?? 'N/A',
                'due' => optional($item->follow_up_datetime)->format('d M, h:i A'),
            ]))
            ->merge(\App\Models\Meeting::active()->with(['customer', 'assignedUser'])->where('branch_id', $dashboardBranchId)->whereBetween('scheduled_on', [$today, $today->copy()->addDays(7)->endOfDay()])->orderBy('scheduled_on')->take(4)->get()->map(fn ($item) => [
                'work' => 'Meeting',
                'party' => $item->customer?->name ?? $item->meeting_title ?? 'N/A',
                'owner' => $item->assignedUser?->name ?? 'N/A',
                'due' => optional($item->scheduled_on)->format('d M, h:i A'),
            ]))
            ->sortBy('due')
            ->take(4);

        $activityMonths = collect(range(5, 0))->map(fn ($offset) => $today->copy()->subMonths($offset));
        $crmActivity = $activityMonths->map(function ($month) use ($dashboardBranchId) {
            return [
                'label' => $month->format('M y'),
                'leads' => \App\Models\Lead::active()->where('branch_id', $dashboardBranchId)->whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->count(),
                'followups' => \App\Models\FollowUp::active()->where('branch_id', $dashboardBranchId)->whereMonth('follow_up_datetime', $month->month)->whereYear('follow_up_datetime', $month->year)->count(),
                'meetings' => \App\Models\Meeting::active()->where('branch_id', $dashboardBranchId)->whereMonth('scheduled_on', $month->month)->whereYear('scheduled_on', $month->year)->count(),
            ];
        });
        $crmMaxActivity = max(1, $crmActivity->flatMap(fn ($item) => [$item['leads'], $item['followups'], $item['meetings']])->max() ?? 1);

        $staffQuery = \App\Models\User::where('role', 'staff')->where('isDeleted', '!=', 1)->where('branch_id', $dashboardBranchId);
        $staffTotal = (clone $staffQuery)->count();
        $staffJoinedThisMonth = (clone $staffQuery)->whereMonth('created_at', $today->month)->whereYear('created_at', $today->year)->count();
        $todayAttendance = \App\Models\Attendance::where('branch_id', $dashboardBranchId)->whereDate('date', $today)->get();
        $todayPresent = $todayAttendance->whereIn('status', ['Present', 'present'])->count();
        $todayAbsent = max(0, $staffTotal - $todayPresent);
        $monthlyAttendanceTotal = \App\Models\Attendance::where('branch_id', $dashboardBranchId)->whereMonth('date', $today->month)->whereYear('date', $today->year)->count();
        $monthlyPresentTotal = \App\Models\Attendance::where('branch_id', $dashboardBranchId)->whereMonth('date', $today->month)->whereYear('date', $today->year)->whereIn('status', ['Present', 'present'])->count();
        $monthlyAttendanceRate = $monthlyAttendanceTotal > 0 ? round(($monthlyPresentTotal / $monthlyAttendanceTotal) * 100) : 0;
        $currentMonthSalaries = \App\Models\Salary::with('staff')->where('branch_id', $dashboardBranchId)->where('month', $today->month)->where('year', $today->year)->get();
        $paidSalaryTotal = $currentMonthSalaries->where('status', 'Paid')->sum('total_salary');
        $paidStaffCount = $currentMonthSalaries->where('status', 'Paid')->count();
        $pendingPayrollCount = $currentMonthSalaries->where('status', '!=', 'Paid')->count();

        $attendanceDays = collect(range(6, 0))->map(function ($offset) use ($today, $dashboardBranchId) {
            $date = $today->copy()->subDays($offset);
            $records = \App\Models\Attendance::where('branch_id', $dashboardBranchId)->whereDate('date', $date)->get();
            return [
                'label' => $date->format('d M'),
                'present' => $records->whereIn('status', ['Present', 'present'])->count(),
                'half' => $records->whereIn('status', ['Half-day', 'Half Day', 'half-day'])->count(),
                'absent' => $records->whereIn('status', ['Absent', 'absent'])->count(),
            ];
        });
        $attendanceMax = max(1, $attendanceDays->flatMap(fn ($item) => [$item['present'], $item['half'], $item['absent']])->max() ?? 1);
        $payrollTrend = $activityMonths->map(function ($month) use ($dashboardBranchId) {
            $records = \App\Models\Salary::where('branch_id', $dashboardBranchId)->where('month', $month->month)->where('year', $month->year)->get();
            return [
                'label' => $month->format('M y'),
                'payout' => (float) $records->sum('total_salary'),
                'paid_staff' => $records->where('status', 'Paid')->count(),
            ];
        });
        $payrollMax = max(1, $payrollTrend->max('payout') ?? 1);
        $attendanceWatch = (clone $staffQuery)->take(4)->get()->map(function ($staff) use ($todayAttendance) {
            $record = $todayAttendance->firstWhere('user_id', $staff->id);
            return [
                'name' => $staff->name,
                'present' => $record && in_array($record->status, ['Present', 'present'], true) ? 1 : 0,
                'absent' => $record ? (in_array($record->status, ['Absent', 'absent'], true) ? 1 : 0) : 1,
            ];
        });
    @endphp
    {{-- <style>
        .color_box {
            display: block;
            width: 100%;
            text-decoration: none;
            color: inherit;
        }

        /* Dashboard widget styles remain the same */
        @media screen and (min-width: 768px) and (max-width: 1400px) {
            .dash-count .dash-imgs svg {
                width: 40px !important;
                height: 36px !important;
            }

            .dash-widgetcontent h6 {
                font-size: 10px !important;
            }

            .dash-counts h5 {
                font-size: 10.5px !important;
            }

            .dash-count {
                min-height: 90px !important;
                margin: 0 0 15px !important;
                padding: 17px;
            }
        }

        @media screen and (max-width: 767px) {
            .dash-widget {
                display: flex;
                flex-direction: column;
                align-items: center;
                margin-bottom: 15px !important;
                text-align: center;
                padding: 12px 20px !important;
            }

            .col-4 {
                display: flex;
                justify-content: center;
            }

            .dash-widgetcontent {
                margin-left: 0 !important;
                width: 68px !important;
            }

            .dash-widgetcontent h5 {
                margin-top: .5rem !important;
                font-size: 13px !important;
            }

            .dash-widgetcontent h6 {
                font-size: 10px !important;
            }

            .dash-count {
                min-height: 90px !important;
                margin: 0 0 15px !important;
                padding: 15px;
            }

            .dash-counts h5 {
                font-size: 10.5px !important;
            }

            .dash-count .dash-imgs svg {
                width: 37px !important;
                height: 37px !important;
            }

            .dash-count h4 {
                font-size: 19px !important;
            }

            .table-responsive {
                font-size: 12px !important;
            }

            /* NEW: Mobile table styles for sales/purchases */
            .dataview table thead th:nth-child(n+3),
            .dataview table tbody td:nth-child(n+3) {
                display: none !important;
            }

            .dataview table thead th.details-column,
            .dataview table tbody td:nth-child(2) {
                display: table-cell !important;
                text-align: center;
                vertical-align: middle;
                width: 50px;
            }

            .dataview .toggle-details i {
                font-size: 18px;
                transition: transform 0.3s ease;
            }

            /* Order ID column styling */
            .dataview table tbody td:first-child {
                display: flex !important;
                align-items: center !important;
                max-width: calc(100vw - 100px) !important;
            }

            .dataview .order-id {
                display: inline-block !important;
                max-width: calc(100% - 50px) !important;
                margin-left: 8px !important;
                font-size: 14px !important;
                word-break: break-word !important;
            }
        }

        /* Desktop: hide details toggle column */
        @media (min-width: 769px) {

            .dataview table thead th.details-column,
            .dataview table tbody td:nth-child(2) {
                display: none !important;
            }
        }

        /* Mobile collapse styles */
        .mobile-details-collapse {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-top: 10px;
            padding: 12px;
            background-color: #f8f9fa;
        }

        .mobile-details-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #dee2e6;
        }

        .mobile-details-label {
            font-weight: 600;
            color: #495057;
        }

        .mobile-details-value {
            color: #212529;
            text-align: right;
        }
    </style> --}}
<style>
    .color_box {
        display: block;
        width: 100%;
        text-decoration: none;
        color: inherit;
    }

    /* Dashboard widget styles */
    .dash-widget {
        transition: all 0.3s ease;
        border-radius: 8px !important;
    }

    .dash-widget:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    /* Tablet view (768px - 1400px) */
    @media screen and (min-width: 768px) and (max-width: 1400px) {
        .dash-widget {
            padding: 15px !important;
        }

        .dash-widgetimg span img {
            width: 40px !important;
            height: 36px !important;
        }

        .dash-widgetcontent h6 {
            font-size: 10px !important;
            line-height: 1.2 !important;
        }

        .dash-widgetcontent h5 {
            font-size: 10.5px !important;
            margin-bottom: 5px !important;
        }

        .dash-widget {
            min-height: 90px !important;
            margin: 0 0 15px !important;
        }

        /* Ensure proper alignment in tablet */
        .col-lg-4.col-sm-6.col-4 {
            padding-left: 7.5px !important;
            padding-right: 7.5px !important;
        }
    }

    /* Mobile view (max-width: 767px) */
    @media screen and (max-width: 767px) {

         .dash-count {
                min-height: 90px !important;
                margin: 0 0 15px !important;
                padding: 15px;
            }

            .dash-counts h5 {
                font-size: 10.5px !important;
            }

            .dash-count .dash-imgs svg {
                width: 37px !important;
                height: 37px !important;
            }

            .dash-count h4 {
                font-size: 19px !important;
            }

        /* Dashboard widgets container */
        .row {
            margin-left: -5px !important;
            margin-right: -5px !important;
            display: flex !important;
            flex-wrap: wrap !important;
        }

        /* First row (Purchase, Expense, Sales) - 3 in one row */
        .col-lg-4.col-sm-6.col-4:first-child,
        .col-lg-4.col-sm-6.col-4:nth-child(2),
        .col-lg-4.col-sm-6.col-4:nth-child(3) {
            width: 33.333% !important; /* Equal width for 3 widgets */
            padding-left: 5px !important;
            padding-right: 5px !important;
            margin-bottom: 10px !important;
            float: left !important;
        }

        /* Remaining widgets (Invoice counts, Vendors, Customers) - 2 per row */
        .col-lg-4.col-sm-6.col-4:nth-child(4),
        .col-lg-4.col-sm-6.col-4:nth-child(5),
        .col-lg-4.col-sm-6.col-4:nth-child(6),
        .col-lg-4.col-sm-6.col-4:nth-child(7) {
            width: 50% !important;
            padding-left: 5px !important;
            padding-right: 5px !important;
            margin-bottom: 10px !important;
            float: left !important;
        }

        /* Clear floats */
        .row::after {
            content: "";
            display: table;
            clear: both;
        }

        /* Dashboard widget styling */
        .dash-widget {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            text-align: center !important;
            padding: 12px 5px !important;
            margin-bottom: 0 !important;
            height: 110px !important; /* Slightly shorter for 3 in a row */
            justify-content: center !important;
        }

        .dash-widgetimg {
            margin-bottom: 6px !important;
        }

        .dash-widgetimg span img {
            width: 30px !important;
            height: 30px !important;
        }

        .dash-widgetcontent {
            margin-left: 0 !important;
            width: 100% !important;
            padding: 0 3px !important;
        }

        .dash-widgetcontent h5 {
            margin-top: 0.2rem !important;
            margin-bottom: 0.2rem !important;
            font-size: 12px !important;
            line-height: 1.2 !important;
            word-break: break-word !important;
        }

        .dash-widgetcontent h6 {
            font-size: 10px !important;
            line-height: 1.2 !important;
            margin-bottom: 0 !important;
            color: #6c757d !important;
        }

        /* Currency symbol and amount styling */
        .dash-widgetcontent h5 .counters {
            font-size: 12px !important;
            font-weight: 600 !important;
        }

        /* Adjust for first row (financial amounts) */
        .col-lg-4.col-sm-6.col-4:first-child .dash-widgetcontent h5,
        .col-lg-4.col-sm-6.col-4:nth-child(2) .dash-widgetcontent h5,
        .col-lg-4.col-sm-6.col-4:nth-child(3) .dash-widgetcontent h5 {
            font-size: 11px !important; /* Slightly smaller for amounts */
        }

        .col-lg-4.col-sm-6.col-4:first-child .dash-widgetcontent h6,
        .col-lg-4.col-sm-6.col-4:nth-child(2) .dash-widgetcontent h6,
        .col-lg-4.col-sm-6.col-4:nth-child(3) .dash-widgetcontent h6 {
            font-size: 9px !important; /* Slightly smaller labels */
        }

        /* Adjust for count widgets (4th onwards) */
        .col-lg-4.col-sm-6.col-4:nth-child(n+4) .dash-widgetcontent h5 {
            font-size: 14px !important; /* Larger for counts */
        }

        .col-lg-4.col-sm-6.col-4:nth-child(n+4) .dash-widgetcontent h6 {
            font-size: 11px !important;
        }

        .col-lg-4.col-sm-6.col-4:nth-child(n+4) .dash-widget {
            height: 100px !important; /* Slightly shorter for count widgets */
        }

        /* Table responsive styles */
        .table-responsive {
            font-size: 12px !important;
        }

        /* Mobile table styles for sales/purchases and dashboard detail tables */
        .dataview table thead th:nth-child(n+3),
        .dataview table tbody td:nth-child(n+3),
        .mobile-toggle-table table thead th:nth-child(n+3),
        .mobile-toggle-table table tbody td:nth-child(n+3) {
            display: none !important;
        }

        .dataview table thead th.details-column,
        .dataview table tbody td:nth-child(2),
        .mobile-toggle-table table thead th.details-column,
        .mobile-toggle-table table tbody td:nth-child(2) {
            display: table-cell !important;
            text-align: center;
            vertical-align: top !important;
            width: 50px;
            padding-top: 10px;
        }

        .dataview .toggle-details i,
        .mobile-toggle-table .toggle-details i {
            font-size: 18px;
            transition: transform 0.3s ease;
        }

        .dataview .toggle-details,
        .mobile-toggle-table .toggle-details {
            display: inline-flex;
            align-items: flex-start;
            justify-content: center;
            line-height: 1;
        }

        /* First column styling */
        .dataview table tbody td:first-child,
        .mobile-toggle-table table tbody td:first-child {
            display: flex !important;
            align-items: center !important;
            max-width: calc(100vw - 100px) !important;
        }

        .dataview .order-id,
        .mobile-toggle-table .order-id {
            display: inline-block !important;
            max-width: calc(100% - 50px) !important;
            margin-left: 8px !important;
            font-size: 14px !important;
            word-break: break-word !important;
        }

        .mobile-toggle-table .order-id {
            margin-left: 0 !important;
        }
    }

    /* Desktop: hide details toggle column */
    @media (min-width: 768px) {
        .dataview table thead th.details-column,
        .dataview table tbody td:nth-child(2),
        .mobile-toggle-table table thead th.details-column,
        .mobile-toggle-table table tbody td:nth-child(2) {
            display: none !important;
        }

        /* Desktop widget spacing */
        .col-lg-4.col-sm-6.col-4 {
            /* margin-bottom: 25px !important; */
        }
    }

    @media screen and (min-width: 768px) and (max-width: 1180px) {
        .content > .row:first-child > div.col-lg-4.col-sm-6.col-4 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
            padding-left: 8px !important;
            padding-right: 8px !important;
            margin-bottom: 12px !important;
        }

        .content > .row:first-child > div.col-lg-4.col-sm-6.col-4 .dash-widget {
            min-height: 86px;
            margin: 0 !important;
        }

        .content > .row:first-child > div.d-flex {
            flex: 0 0 50%;
            max-width: 50%;
            padding-left: 8px !important;
            padding-right: 8px !important;
            margin-bottom: 12px !important;
        }

        .content > .row:first-child > div.d-flex .color_box {
            width: 100%;
            height: 100%;
        }

        .content > .row:first-child > div.d-flex .dash-count {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 86px;
            height: 100%;
            margin: 0 !important;
            padding: 14px 16px;
        }

        .content > .row:first-child > div.d-flex .dash-counts h4 {
            margin-bottom: 4px;
        }

        .content > .row:first-child > div.d-flex .dash-counts h5 {
            margin-bottom: 0;
            line-height: 1.25;
        }
    }

    /* Gap between Latest Sales and Latest Purchases cards on mobile */
    @media screen and (max-width: 767px) {
        .col-md-6 {
            margin-bottom: 15px !important;
        }
    }

    /* Mobile collapse styles */
    .mobile-details-collapse {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-top: 10px;
        padding: 12px;
        background-color: #f8f9fa;
    }

    .mobile-details-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        padding-bottom: 8px;
        border-bottom: 1px dashed #dee2e6;
    }

    .mobile-details-label {
        font-weight: 600;
        color: #495057;
    }

    .mobile-details-value {
        color: #212529;
        text-align: right;
    }

    /* Latest Sales & Latest Purchases — product name word wrap */
    .dataview table tbody td.productimgname a span {
        white-space: normal !important;
        overflow: visible !important;
        text-overflow: unset !important;
        word-break: break-word !important;
    }
    .chart-select-container .select2-container--default .select2-selection--single {
        height: 31px !important;
        border: 1px solid #ced4da !important;
        border-radius: 4px !important;
    }

    .chart-select-container .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 29px !important;
        font-size: 13px !important;
        padding-left: 10px !important;
        padding-right: 25px !important;
    }

    .chart-select-container .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 29px !important;
        width: 10px !important;
    }

    /* Clearfix for mobile grid */
    @media screen and (max-width: 767px) {
        .dashboard-widgets-row::after {
            content: "";
            display: table;
            clear: both;
        }
    }

    .overview-section {
        margin-top: 18px;
    }

    .overview-section .overview-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .overview-section .overview-title h4 {
        margin-bottom: 3px;
        font-size: 16px;
        font-weight: 700;
    }

    .overview-section .overview-title p {
        margin: 0;
        color: #637381;
        font-size: 12px;
    }

    .overview-section .metric-card,
    .overview-section .panel-card {
        background: #fff;
        border: 1px solid #d9e8ff;
        border-radius: 4px;
        padding: 14px;
        height: 100%;
    }

    .overview-section.hr-overview .metric-card,
    .overview-section.hr-overview .panel-card {
        border-color: #c9efd7;
    }

    .overview-section .metric-link {
        display: block;
        color: inherit;
        text-decoration: none;
        height: 100%;
    }

    .overview-section .metric-link:hover,
    .overview-section .metric-link:focus {
        color: inherit;
        text-decoration: none;
    }

    .overview-section .metric-link .metric-card {
        transition: transform .15s ease, box-shadow .15s ease;
        cursor: pointer;
    }

    .overview-section .metric-link:hover .metric-card,
    .overview-section .metric-link:focus .metric-card {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(9, 44, 76, 0.08);
    }

    .overview-section .metric-label {
        color: #092c4c;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    .overview-section .metric-value {
        color: #000;
        font-size: 22px;
        font-weight: 800;
        line-height: 1.2;
    }

    .overview-section .metric-help {
        color: #637381;
        font-size: 11px;
    }

    .overview-section .mini-chart {
        display: flex;
        align-items: end;
        gap: 12px;
        height: 180px;
        padding: 20px 10px 6px;
        border-top: 1px solid #f1f3f5;
    }

    .overview-section .mini-chart .chart-month {
        flex: 1;
        min-width: 34px;
        text-align: center;
    }

    .overview-section .mini-chart .bar-stack {
        display: flex;
        align-items: end;
        justify-content: center;
        gap: 3px;
        height: 135px;
    }

    .overview-section .mini-chart .bar {
        width: 7px;
        min-height: 2px;
        border-radius: 8px 8px 0 0;
        display: inline-block;
    }

    .overview-section .mini-chart .bar.bar-wide {
        width: 12px;
    }

    .overview-section .mini-chart .month-label {
        color: #637381;
        font-size: 10px;
        margin-top: 6px;
        white-space: nowrap;
    }

    .overview-section .chart-legend {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        font-size: 10px;
        color: #637381;
    }

    .overview-section .legend-dot {
        width: 8px;
        height: 8px;
        display: inline-block;
        border-radius: 50%;
        margin-right: 4px;
    }

    .overview-section .status-mix-chart {
        padding: 28px 18px 18px;
        text-align: center;
    }

    .overview-section .status-mix-donut {
        width: 180px;
        height: 180px;
        border-radius: 50%;
        margin: 0 auto;
        position: relative;
    }

    .overview-section .status-mix-donut::after {
        content: '';
        position: absolute;
        inset: 28px;
        background: #fff;
        border-radius: 50%;
    }

    .overview-section .status-mix-empty {
        width: 180px;
        height: 180px;
        border-radius: 50%;
        margin: 0 auto;
        border: 18px solid #e9ecef;
    }

    .overview-section .status-mix-legend {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 10px 18px;
        margin-top: 20px;
        color: #092c4c;
        font-size: 12px;
    }

    .overview-section .status-mix-legend-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .overview-section .status-mix-legend-text {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .overview-section .status-mix-legend-share {
        color: #637381;
        font-size: 11px;
    }

    .overview-section .crm-table-scroll {
        max-height: 220px;
        overflow-y: auto;
    }

    .overview-section .crm-table-scroll::-webkit-scrollbar {
        width: 4px;
    }

    .overview-section .crm-table-scroll::-webkit-scrollbar-track {
        background: #f1f3f5;
        border-radius: 4px;
    }

    .overview-section .crm-table-scroll::-webkit-scrollbar-thumb {
        background: #ff9f43;
        border-radius: 4px;
    }
        display: inline-block;
        padding: 3px 8px;
        border-radius: 10px;
        background: #f4f6f8;
        color: #092c4c;
        font-size: 10px;
        font-weight: 700;
    }
</style>
    <div class="content">
        <div class="row">

            {{-- ERP SECTION: Financial Boxes --}}
            @if ($dashboardSettings['erp_section_enabled'] === 'Enable')
                {{-- Total Sales Box --}}
                @if (in_array($role, ['inventory-manager', 'admin', 'sub-admin']) && $dashboardSettings['erp_total_sales_box'] === 'Enable')
                    <div class="col-lg-4 col-sm-6 col-4">
                        <a href="{{ route('sales.list') }}">
                            <div class="dash-widget dash1">
                                <div class="dash-widgetimg">
                                    <span><img src="{{ env('ImagePath') . 'admin/assets/img/icons/dash2.svg' }}"
                                            alt="img"></span>
                                </div>
                                <div class="dash-widgetcontent">
                                    <h5>
                                        @if ($currencyPosition === 'left')
                                            {{ $currencySymbol }}
                                        @endif
                                        <span class="counters" data-count="{{ $totalSalesAmount }}">
                                            {{ number_format($totalSalesAmount, 2) }}</span>
                                        @if ($currencyPosition === 'right')
                                            {{ $currencySymbol }}
                                        @endif
                                    </h5>
                                    <h6>Total Sales Amount</h6>
                                </div>
                            </div>
                        </a>
                    </div>
                @endif
                {{-- Total Purchase Box --}}
                @if (in_array($role, ['inventory-manager', 'admin', 'sub-admin']) && $dashboardSettings['erp_total_purchase_box'] === 'Enable')
                    <div class="col-lg-4 col-sm-6 col-4">
                        <a href="{{ route('purchase.lists') }}">
                            <div class="dash-widget">
                                <div class="dash-widgetimg">
                                    <span><img src="{{ env('ImagePath') . 'admin/assets/img/icons/dash1.svg' }}"
                                            alt="img"></span>
                                </div>
                                <div class="dash-widgetcontent">
                                    <h5>
                                        @if ($currencyPosition === 'left')
                                            {{ $currencySymbol }}
                                        @endif
                                        <span class="counters" data-count="{{ $totalPurchaseAmount }}">
                                            {{ number_format($totalPurchaseAmount, 2) }}
                                        </span>
                                    @if ($currencyPosition === 'right')
                                        {{ $currencySymbol }}
                                    @endif
                                </h5>
                                <h6>Total Purchase Amount</h6>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
                {{-- Total Expense Box --}}
                @if (in_array($role, ['inventory-manager', 'admin', 'sub-admin']) && $dashboardSettings['erp_total_expense_box'] === 'Enable')
                    <div class="col-lg-4 col-sm-6 col-4">
                        <a href="{{ route('expense.list') }}">
                            <div class="dash-widget dash2">
                                <div class="dash-widgetimg">
                                    <span><img src="{{ env('ImagePath') . 'admin/assets/img/icons/dash3.svg' }}"
                                            alt="img"></span>
                                </div>
                                <div class="dash-widgetcontent">
                                    <h5>
                                        @if ($currencyPosition === 'left')
                                            {{ $currencySymbol }}
                                        @endif
                                        <span class="counters"
                                            data-count="{{ $totalExpenseAmount }}">{{ number_format($totalExpenseAmount, 2) }}</span>
                                        @if ($currencyPosition === 'right')
                                            {{ $currencySymbol }}
                                        @endif
                                    </h5>
                                    <h6>Total Expense Amount</h6>
                                </div>
                            </div>
                        </a>
                    </div>
                @endif
            @endif
            {{-- END ERP SECTION: Financial Boxes --}}
            @if (in_array($role, ['sales-manager']))
                <div @class([
                    'col-lg-3 col-sm-6 col-6 d-flex' => in_array($role, [
                        'inventory-manager',
                        'admin',
                        'sub-admin',
                    ]),
                    'col-lg-4 col-sm-6 col-6 d-flex' => $role === 'sales-manager',
                ])>
                    <a href="{{ route('sales.list') }}" class="color_box box4">
                        <div class="dash-count das1">
                            <div class="dash-counts">
                                <h4>
                                    @if ($currencyPosition === 'left')
                                        {{ $currencySymbol }}
                                    @endif
                                    <span class="counters"
                                        data-count="{{ $totalSalesAmount }}">{{ number_format($totalSalesAmount, 2) }}</span>
                                    @if ($currencyPosition === 'right')
                                        {{ $currencySymbol }}
                                    @endif
                                </h4>
                                <h5>Total Sales Amount</h5>
                            </div>
                            <div class="dash-imgs">
                                <i data-feather="user-check"></i>
                            </div>
                        </div>
                    </a>
                </div>
            @endif

            {{-- ERP SECTION: Count Boxes --}}
            @if ($dashboardSettings['erp_section_enabled'] === 'Enable')
                {{-- Sales Invoice Count --}}
                @if (in_array($role, ['inventory-manager', 'sales-manager', 'admin', 'sub-admin']) && $dashboardSettings['erp_sales_invoice_count_box'] === 'Enable')
                    <div @class([
                        'col-lg-3 col-sm-6 col-6 d-flex' => in_array($role, [
                            'inventory-manager',
                            'admin',
                            'sub-admin',
                        ]),
                        'col-lg-4 col-sm-6 col-6 d-flex' => $role === 'sales-manager',
                    ])>
                        <a href="{{ route('sales.list') }}" class="color_box box1">
                            <div class="dash-count das3">
                                <div class="dash-counts">
                                    <h4>{{ number_format($salesInvoiceCount, 0) }}</h4>
                                    <h5>Sales Invoice</h5>
                                </div>
                                <div class="dash-imgs">
                                    <i data-feather="file"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                @endif
                {{-- Purchase Invoice Count --}}
                @if (in_array($role, ['inventory-manager', 'purchase-manager', 'admin', 'sub-admin']) && $dashboardSettings['erp_purchase_invoice_count_box'] === 'Enable')
                    <div @class([
                        'col-lg-3 col-sm-6 col-6 d-flex' => in_array($role, [
                            'inventory-manager',
                            'admin',
                            'sub-admin',
                        ]),
                        'col-lg-4 col-sm-6 col-6 d-flex' => $role === 'purchase-manager',
                    ])>
                        <a href="{{ route('purchase.lists') }}" class="color_box box2">
                            <div class="dash-count das2">
                                <div class="dash-counts">
                                    <h4>{{ number_format($purchaseInvoiceCount, 0) }}</h4>
                                    <h5>Purchase Invoice</h5>
                                </div>
                                <div class="dash-imgs">
                                    <i data-feather="file-text"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                @endif
                {{-- Customers Count --}}
                @if (in_array($role, ['inventory-manager', 'sales-manager', 'admin', 'sub-admin']) && $dashboardSettings['erp_customers_count_box'] === 'Enable')
                    <div @class([
                        'col-lg-3 col-sm-6 col-6 d-flex' => in_array($role, [
                            'inventory-manager',
                            'admin',
                            'sub-admin',
                        ]),
                        'col-lg-4 col-sm-6 col-6 d-flex' => $role === 'sales-manager',
                    ])>
                        <a href="{{ route('customer.list') }}" class="color_box box3">
                            <div class="dash-count">
                                <div class="dash-counts">
                                    <h4>{{ number_format($customerCount, 0) }}</h4>
                                    <h5>Customers</h5>
                                </div>
                                <div class="dash-imgs">
                                    <i data-feather="user"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                @endif
                {{-- Vendors Count --}}
                @if (in_array($role, ['inventory-manager', 'purchase-manager', 'admin', 'sub-admin']) && $dashboardSettings['erp_vendors_count_box'] === 'Enable')
                    <div @class([
                        'col-lg-3 col-sm-6 col-6 d-flex' => in_array($role, [
                            'inventory-manager',
                            'admin',
                            'sub-admin',
                        ]),
                        'col-lg-4 col-sm-6 col-6 d-flex' => $role === 'purchase-manager',
                    ])>
                        <a href="{{ route('vendor.list') }}" class="color_box box4">
                            <div class="dash-count das1">
                                <div class="dash-counts">
                                    <h4>{{ number_format($vendorCount, 0) }}</h4>
                                    <h5>Vendors</h5>
                                </div>
                                <div class="dash-imgs">
                                    <i data-feather="user-check"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                @endif
            @endif
            {{-- END ERP SECTION: Count Boxes --}}

        </div>

        @php
            use Carbon\Carbon;
            $currentYear = Carbon::now()->year;
            $previousYear = $currentYear - 1;
        @endphp

        <div class="row">
            {{-- ERP SECTION: Charts --}}
            @if ($dashboardSettings['erp_section_enabled'] === 'Enable')
                {{-- Sales Chart --}}
                @if (in_array($role, ['inventory-manager', 'sales-manager', 'admin', 'sub-admin']) && $dashboardSettings['erp_sales_chart'] === 'Enable')
                    <div class="col-lg-6 col-md-6 col-sm-12 col-12 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div class="card-title mb-0">Sales</div>
                                <div class="chart-select-container">
                                    <select class="form-control form-control-sm chart-select2" id="salesYearSelect" style="width: 130px;">
                                        <option value="month">This month ({{ date('F') }})</option>
                                        <option value="{{ $previousYear }}">Previous year ({{ $previousYear }})</option>
                                        <option value="{{ $currentYear }}" selected>This year ({{ $currentYear }})</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-body chart-set">
                                <div class="h-250" style="width: 100%;" id="saleschart"></div>
                            </div>
                        </div>
                    </div>
                @endif
                {{-- Purchase Chart --}}
                @if (in_array($role, ['inventory-manager', 'purchase-manager', 'admin', 'sub-admin']) && $dashboardSettings['erp_purchase_chart'] === 'Enable')
                    <div class="col-lg-6 col-md-6 col-sm-12 col-12 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div class="card-title mb-0">Purchases</div>
                                <div class="chart-select-container">
                                    <select class="form-control form-control-sm chart-select2" id="purchaseYearSelect" style="width: 130px;">
                                        <option value="month">This month ({{ date('F') }})</option>
                                        <option value="{{ $previousYear }}">Previous year ({{ $previousYear }})</option>
                                        <option value="{{ $currentYear }}" selected>This year ({{ $currentYear }})</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-body chart-set">
                                <div class="h-250" style="width: 100%;" id="purchasechart"></div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
            {{-- END ERP SECTION: Charts --}}

            {{-- ERP SECTION: Tables --}}
            @if ($dashboardSettings['erp_section_enabled'] === 'Enable')
                {{-- Latest Sales Table --}}
                @if (in_array($role, ['inventory-manager', 'sales-manager', 'admin', 'sub-admin']) && $dashboardSettings['erp_latest_sales_table'] === 'Enable')
                    <div class="col-md-6">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 class="card-title mb-0">Latest Sales</h4>
                                    <a href="{{ route('sales.list') }}" class="btn btn-sm btn-primary">View All</a>
                                </div>
                                <div class="table-responsive dataview">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                            <th class="details-column">Details</th>
                                            <th>Product Name</th>
                                            <th>Grand Total</th>
                                            <th>Sale Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($latestSales as $index => $item)
                                            <tr>
                                                <td>
                                                    <div>
                                                        <div style="display: flex; align-items: center;">
                                                            <a href="/sales-details/{{ $item->order_id }}"
                                                                class="d-flex align-items-center text-decoration-none">
                                                                <span
                                                                    class="order-id">{{ $item->order_number ?? 'N/A' }}</span>
                                                            </a>
                                                        </div>

                                                        <!-- Collapsible Details for Mobile -->
                                                        <div class="collapse mobile-details-collapse d-md-none"
                                                            id="sales-details-{{ $item->order_id ?? $index }}">
                                                            <div class="mobile-details-row">
                                                                <span class="mobile-details-label">Product:</span>
                                                                <span class="mobile-details-value">
                                                                    @php
                                                                        $images = json_decode($item->images, true);
                                                                        $imagePath =
                                                                            !empty($images) && isset($images[0])
                                                                                ? env('ImagePath') .
                                                                                    'storage/' .
                                                                                    $images[0]
                                                                                : env('ImagePath') .
                                                                                    '/admin/assets/img/product/noimage.png';
                                                                    @endphp
                                                                    <div
                                                                        style="display: flex; align-items: center; justify-content: flex-end; gap: 8px;">
                                                                        <span>{{ $item->product_name ?? 'N/A' }}</span>
                                                                        <img src="{{ asset($imagePath) }}" alt="Product"
                                                                            style="width: 30px; height: 30px; object-fit: cover; border-radius: 4px;">
                                                                    </div>
                                                                </span>
                                                            </div>
                                                            <div class="mobile-details-row">
                                                                <span class="mobile-details-label">Grand Total:</span>
                                                                <span class="mobile-details-value">
                                                                    @if ($currencyPosition === 'left')
                                                                        {{ $currencySymbol }}
                                                                    @endif
                                                                    {{ number_format($item->total_amount ?? 0, 2) }}
                                                                    @if ($currencyPosition === 'right')
                                                                        {{ $currencySymbol }}
                                                                    @endif
                                                                </span>
                                                            </div>
                                                            <div class="mobile-details-row">
                                                                <span class="mobile-details-label">Sale Date:</span>
                                                                <span class="mobile-details-value">
                                                                    {{ Carbon::parse($item->order_date)->format('d F Y') }}
                                                                </span>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </td>

                                                <td class="details-column">
                                                    <a href="#sales-details-{{ $item->order_id ?? $index }}"
                                                        class="toggle-details" data-bs-toggle="collapse">
                                                        <i class="fas fa-plus-circle" style="color: #ff9f43;"></i>
                                                    </a>
                                                </td>

                                                <td class="productimgname d-none d-md-table-cell">
                                                    <a href="/product-view/{{ $item->product_id }}"
                                                        class="d-flex align-items-center"
                                                        style="max-width: 250px; text-decoration: none; color: inherit;">
                                                        <img src="{{ asset($imagePath) }}" alt="Product Image"
                                                            style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; margin-right: 10px; flex-shrink: 0;">
                                                        <span
                                                            style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1;">
                                                            {{ $item->product_name ?? 'N/A' }}
                                                        </span>
                                                    </a>
                                                </td>

                                                <td class="d-none d-md-table-cell">
                                                    @if ($currencyPosition === 'left')
                                                        {{ $currencySymbol }}
                                                    @endif
                                                    {{ number_format($item->total_amount ?? 0, 2) }}
                                                    @if ($currencyPosition === 'right')
                                                        {{ $currencySymbol }}
                                                    @endif
                                                </td>

                                                <td class="d-none d-md-table-cell">
                                                    {{ Carbon::parse($item->order_date)->format('d F Y') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No sales records found
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Latest Purchases Table --}}
                @if (in_array($role, ['inventory-manager', 'purchase-manager', 'admin', 'sub-admin']) && $dashboardSettings['erp_latest_purchases_table'] === 'Enable')
                    <div class="col-md-6">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 class="card-title mb-0">Latest Purchases</h4>
                                    <a href="{{ route('purchase.lists') }}" class="btn btn-sm btn-primary">View All</a>
                                </div>
                                <div class="table-responsive dataview">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Bill No</th>
                                                <th class="details-column">Details</th>
                                                <th>Product Name</th>
                                                <th>Grand Total</th>
                                                <th>Purchase Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @forelse ($latestPurchases as $index => $item)
                                            <tr>
                                                <td>
                                                    <div>
                                                        <div style="display: flex; align-items: center;">
                                                            <a href="/print-purchase/{{ $item->invoice_id }}"
                                                                class="d-flex align-items-center text-decoration-none">
                                                                <span
                                                                    class="order-id">{{ $item->bill_no ?? 'N/A' }}</span>
                                                            </a>
                                                        </div>

                                                        <!-- Collapsible Details for Mobile -->
                                                        <div class="collapse mobile-details-collapse d-md-none"
                                                            id="purchase-details-{{ $item->invoice_id ?? $index }}">
                                                            <div class="mobile-details-row">
                                                                <span class="mobile-details-label">Product:</span>
                                                                <span class="mobile-details-value">
                                                                    @php
                                                                        $images = json_decode($item->images, true);
                                                                        $imagePath = !empty($images[0])
                                                                            ? env('ImagePath') . 'storage/' . $images[0]
                                                                            : env('ImagePath') .
                                                                                'admin/assets/img/product/noimage.png';
                                                                    @endphp
                                                                    <div
                                                                        style="display: flex; align-items: center; justify-content: flex-end; gap: 8px;">
                                                                        <span>{{ $item->product_name ?? 'N/A' }}</span>
                                                                        <img src="{{ asset($imagePath) }}" alt="Product"
                                                                            style="width: 30px; height: 30px; object-fit: cover; border-radius: 4px;">
                                                                    </div>
                                                                </span>
                                                            </div>
                                                            <div class="mobile-details-row">
                                                                <span class="mobile-details-label">Grand Total:</span>
                                                                <span class="mobile-details-value">
                                                                    @if ($currencyPosition === 'left')
                                                                        {{ $currencySymbol }}
                                                                    @endif
                                                                    {{ number_format($item->grand_total ?? 0, 2) }}
                                                                    @if ($currencyPosition === 'right')
                                                                        {{ $currencySymbol }}
                                                                    @endif
                                                                </span>
                                                            </div>
                                                            <div class="mobile-details-row">
                                                                <span class="mobile-details-label">Purchase Date:</span>
                                                                <span class="mobile-details-value">
                                                                    {{ \Carbon\Carbon::parse($item->purchase_date)->format('d F Y') }}
                                                                </span>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </td>

                                                <td class="details-column">
                                                    <a href="#purchase-details-{{ $item->invoice_id ?? $index }}"
                                                        class="toggle-details" data-bs-toggle="collapse">
                                                        <i class="fas fa-plus-circle" style="color: #ff9f43;"></i>
                                                    </a>
                                                </td>

                                                <td class="productimgname d-none d-md-table-cell">
                                                    <a href="/product-view/{{ $item->product_id }}"
                                                        style="display: flex; align-items: center; max-width: 250px; text-decoration: none; color: inherit;">
                                                        <img src="{{ asset($imagePath) }}" alt="Product Image"
                                                            style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; margin-right: 10px; flex-shrink: 0;">
                                                        <span
                                                            style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1;">
                                                            {{ $item->product_name ?? 'N/A' }}
                                                        </span>
                                                    </a>
                                                </td>

                                                <td class="d-none d-md-table-cell">
                                                    @if ($currencyPosition === 'left')
                                                        {{ $currencySymbol }}
                                                    @endif
                                                    {{ number_format($item->grand_total ?? 0, 2) }}
                                                    @if ($currencyPosition === 'right')
                                                        {{ $currencySymbol }}
                                                    @endif
                                                </td>

                                                <td class="d-none d-md-table-cell">
                                                    {{ \Carbon\Carbon::parse($item->purchase_date)->format('d F Y') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No purchase records
                                                    found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            @endif
            {{-- END ERP SECTION: Tables --}}
        </div>

        @if ($dashboardSettings['crm_section_enabled'] === 'Enable' && in_array($role, $crmRoles))
            <div class="overview-section crm-overview">
                <div class="overview-title">
                    <div>
                        <h4>CRM Pipeline Overview</h4>
                      
                    </div>
                    <a href="{{ route('lead.list') }}" class="btn btn-sm btn-primary">View Leads</a>
                </div>

                <div class="row g-3">
                    @if ($dashboardSettings['crm_lead_pipeline_box'] === 'Enable')
                        <div class="col-lg-3 col-sm-6 col-6">
                            <a href="{{ route('lead.list') }}" class="metric-link">
                                <div class="metric-card">
                                    <div class="metric-label">Lead Pipeline</div>
                                    <div class="metric-value">{{ $leadTotal }}</div>
                                    <div class="metric-help">{{ $newLeadsThisMonth }} new this month</div>
                                </div>
                            </a>
                        </div>
                    @endif
                    @if ($dashboardSettings['crm_conversion_box'] === 'Enable')
                        <div class="col-lg-3 col-sm-6 col-6">
                            <a href="{{ route('customer.list') }}" class="metric-link">
                                <div class="metric-card">
                                    <div class="metric-label">Conversion</div>
                                    <div class="metric-value">{{ $conversionRate }}%</div>
                                    <div class="metric-help">{{ $convertedLeads }} leads became customers</div>
                                </div>
                            </a>
                        </div>
                    @endif
                    @if ($dashboardSettings['crm_followup_lead_box'] === 'Enable')
                        <div class="col-lg-3 col-sm-6 col-6">
                            <a href="{{ route('followup.list') }}" class="metric-link">
                                <div class="metric-card">
                                    <div class="metric-label">Follow-up Lead</div>
                                    <div class="metric-value">{{ $followUpLeadCount }}</div>
                                    <div class="metric-help">{{ $overdueFollowUps }} overdue and need action</div>
                                </div>
                            </a>
                        </div>
                    @endif
                    @if ($dashboardSettings['crm_meeting_momentum_box'] === 'Enable')
                        <div class="col-lg-3 col-sm-6 col-6">
                            <a href="{{ route('meeting.list') }}" class="metric-link">
                                <div class="metric-card">
                                    <div class="metric-label">Meeting Momentum</div>
                                    <div class="metric-value">{{ $completedMeetingsThisMonth }}</div>
                                    <div class="metric-help">completed this month</div>
                                </div>
                            </a>
                        </div>
                    @endif
                </div>

             

                <div class="row g-3 mt-1">
                    @if ($dashboardSettings['crm_pipeline_quality_table'] === 'Enable')
                        <div class="col-lg-4">
                            <div class="panel-card">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="card-title mb-0">Pipeline Quality</h5>
                                    <a href="{{ route('lead.list') }}" class="btn btn-sm btn-primary">View All</a>
                                </div>
                                <div class="table-responsive mobile-toggle-table crm-table-scroll">
                                    <table class="table mb-0">
                                        <thead><tr><th>Status</th><th class="details-column">Details</th><th>Leads</th><th>Share</th></tr></thead>
                                        <tbody>
                                            @forelse ($leadStatusMix as $status => $total)
                                                <tr>
                                                    <td>
                                                        <div>
                                                            <span class="order-id ms-0">{{ $status }}</span>
                                                            <div class="collapse mobile-details-collapse d-md-none" id="pipeline-quality-{{ $loop->index }}">
                                                                <div class="mobile-details-row">
                                                                    <span class="mobile-details-label">Leads:</span>
                                                                    <span class="mobile-details-value">{{ $total }}</span>
                                                                </div>
                                                                <div class="mobile-details-row">
                                                                    <span class="mobile-details-label">Share:</span>
                                                                    <span class="mobile-details-value">{{ $leadTotal > 0 ? round(($total / $leadTotal) * 100) : 0 }}%</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="details-column">
                                                        <a href="#pipeline-quality-{{ $loop->index }}" class="toggle-details" data-bs-toggle="collapse">
                                                            <i class="fas fa-plus-circle" style="color: #ff9f43;"></i>
                                                        </a>
                                                    </td>
                                                    <td class="d-none d-md-table-cell">{{ $total }}</td>
                                                    <td class="d-none d-md-table-cell">{{ $leadTotal > 0 ? round(($total / $leadTotal) * 100) : 0 }}%</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="text-center text-muted">No pipeline data</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if ($dashboardSettings['crm_recent_leads_table'] === 'Enable')
                        <div class="col-lg-4">
                            <div class="panel-card">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="card-title mb-0">Recent Leads</h5>
                                    <a href="{{ route('lead.list') }}" class="btn btn-sm btn-primary">View All</a>
                                </div>
                                <div class="table-responsive mobile-toggle-table crm-table-scroll">
                                    <table class="table mb-0">
                                        <thead><tr><th>Lead</th><th class="details-column">Details</th><th>Status</th><th>Owner</th></tr></thead>
                                        <tbody>
                                            @forelse ($recentLeads as $lead)
                                                <tr>
                                                    <td>
                                                        <div>
                                                            <span class="order-id ms-0">{{ $lead->name }}</span>
                                                            <div><small>{{ $lead->company_name }}</small></div>
                                                            <div class="collapse mobile-details-collapse d-md-none" id="recent-lead-{{ $lead->id }}">
                                                                <div class="mobile-details-row">
                                                                    <span class="mobile-details-label">Status:</span>
                                                                    <span class="mobile-details-value">{{ $lead->lead_status ?? 'N/A' }}</span>
                                                                </div>
                                                                <div class="mobile-details-row">
                                                                    <span class="mobile-details-label">Owner:</span>
                                                                    <span class="mobile-details-value">{{ $lead->assignedUser?->name ?? 'N/A' }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="details-column">
                                                        <a href="#recent-lead-{{ $lead->id }}" class="toggle-details" data-bs-toggle="collapse">
                                                            <i class="fas fa-plus-circle" style="color: #ff9f43;"></i>
                                                        </a>
                                                    </td>
                                                    <td class="d-none d-md-table-cell"><span class="status-pill">{{ $lead->lead_status ?? 'N/A' }}</span></td>
                                                    <td class="d-none d-md-table-cell">{{ $lead->assignedUser?->name ?? 'N/A' }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="text-center text-muted">No recent leads</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if ($dashboardSettings['crm_next_7_days_table'] === 'Enable')
                        <div class="col-lg-4">
                            <div class="panel-card">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="card-title mb-0">Next 7 Days</h5>
                                    <a href="{{ route('followup.list') }}" class="btn btn-sm btn-primary">View All</a>
                                </div>
                                <div class="table-responsive mobile-toggle-table crm-table-scroll">
                                    <table class="table mb-0">
                                        <thead><tr><th>Work</th><th class="details-column">Details</th><th>Party</th><th>Due</th></tr></thead>
                                        <tbody>
                                            @forelse ($crmNextItems as $item)
                                                <tr>
                                                    <td>
                                                        <div>
                                                            <span class="order-id ms-0">{{ $item['work'] }}</span>
                                                            <div><small>{{ $item['owner'] }}</small></div>
                                                            <div class="collapse mobile-details-collapse d-md-none" id="next-seven-days-{{ $loop->index }}">
                                                                <div class="mobile-details-row">
                                                                    <span class="mobile-details-label">Party:</span>
                                                                    <span class="mobile-details-value">{{ $item['party'] }}</span>
                                                                </div>
                                                                <div class="mobile-details-row">
                                                                    <span class="mobile-details-label">Due:</span>
                                                                    <span class="mobile-details-value">{{ $item['due'] }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="details-column">
                                                        <a href="#next-seven-days-{{ $loop->index }}" class="toggle-details" data-bs-toggle="collapse">
                                                            <i class="fas fa-plus-circle" style="color: #ff9f43;"></i>
                                                        </a>
                                                    </td>
                                                    <td class="d-none d-md-table-cell">{{ $item['party'] }}</td>
                                                    <td class="d-none d-md-table-cell">{{ $item['due'] }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="text-center text-muted">No upcoming CRM work</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                   <div class="row g-3 mt-1">
                    @if ($dashboardSettings['crm_lead_status_mix_chart'] === 'Enable')
                        <div class="col-lg-5 col-md-6">
                            <div class="panel-card">
                                <h5 class="card-title">Lead Status Mix</h5>
                                @php
                                    $leadChartColors = ['#4361ee', '#36c396', '#ff9f43', '#fa6c7c', '#845ef7', '#15aabf', '#f06595'];
                                    $leadSegments = [];
                                    $leadLegendItems = [];
                                    $currentAngle = 0;
                                    $statusIndex = 0;

                                    foreach ($leadStatusMix as $status => $total) {
                                        $color = $leadChartColors[$statusIndex % count($leadChartColors)];
                                        $share = $leadTotal > 0 ? round(($total / $leadTotal) * 100) : 0;
                                        $nextAngle = $leadTotal > 0 ? $currentAngle + (($total / $leadTotal) * 360) : $currentAngle;

                                        $leadSegments[] = $color . ' ' . $currentAngle . 'deg ' . $nextAngle . 'deg';
                                        $leadLegendItems[] = [
                                            'status' => $status,
                                            'total' => $total,
                                            'share' => $share,
                                            'color' => $color,
                                        ];

                                        $currentAngle = $nextAngle;
                                        $statusIndex++;
                                    }

                                    $leadDonutBackground = count($leadSegments) > 0
                                        ? 'conic-gradient(' . implode(', ', $leadSegments) . ')'
                                        : 'conic-gradient(#e9ecef 0deg 360deg)';
                                @endphp

                                <div class="status-mix-chart">
                                    @if (count($leadLegendItems) > 0)
                                        <div class="status-mix-donut" style="background: {{ $leadDonutBackground }};"></div>
                                        <div class="status-mix-legend">
                                            @foreach ($leadLegendItems as $item)
                                                <span class="status-mix-legend-item">
                                                    <i class="legend-dot" style="background:{{ $item['color'] }};"></i>
                                                    <span class="status-mix-legend-text">
                                                        {{ $item['status'] }}
                                                        <span class="status-mix-legend-share">({{ $item['total'] }} / {{ $item['share'] }}%)</span>
                                                    </span>
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="status-mix-empty"></div>
                                        <div class="text-center text-muted mt-3">No leads found</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                    @if ($dashboardSettings['crm_activity_trend_chart'] === 'Enable')
                        <div class="col-lg-7 col-md-6">
                            <div class="panel-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">CRM Activity Trend</h5>
                                    <div class="chart-legend">
                                        <span><i class="legend-dot" style="background:#4361ee;"></i>Leads</span>
                                        <span><i class="legend-dot" style="background:#ff9f43;"></i>Follow-ups</span>
                                        <span><i class="legend-dot" style="background:#00a389;"></i>Meetings</span>
                                    </div>
                                </div>
                                <div class="mini-chart">
                                    @foreach ($crmActivity as $activity)
                                        <div class="chart-month">
                                            <div class="bar-stack">
                                                <span class="bar" style="height: {{ max(2, ($activity['leads'] / $crmMaxActivity) * 125) }}px; background:#4361ee;"></span>
                                                <span class="bar" style="height: {{ max(2, ($activity['followups'] / $crmMaxActivity) * 125) }}px; background:#ff9f43;"></span>
                                                <span class="bar" style="height: {{ max(2, ($activity['meetings'] / $crmMaxActivity) * 125) }}px; background:#00a389;"></span>
                                            </div>
                                            <div class="month-label">{{ $activity['label'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if ($dashboardSettings['hr_section_enabled'] === 'Enable' && in_array($role, $hrRoles))
            <div class="overview-section hr-overview">
                <div class="overview-title">
                    <div>
                        <h4>HR Workforce Overview</h4>
                        
                    </div>
                    <a href="{{ route('staff.list') }}" class="btn btn-sm btn-primary">View Staff</a>
                </div>

                <div class="row g-3">
                    @if ($dashboardSettings['hr_staff_strength_box'] === 'Enable')
                        <div class="col-lg-3 col-sm-6 col-6"><a href="{{ route('staff.list') }}" class="metric-link"><div class="metric-card"><div class="metric-label">Staff Strength</div><div class="metric-value">{{ $staffTotal }}</div><div class="metric-help">{{ $staffJoinedThisMonth }} joined this month</div></div></a></div>
                    @endif
                    @if ($dashboardSettings['hr_active_staff_box'] === 'Enable')
                        <div class="col-lg-3 col-sm-6 col-6"><a href="{{ route('attendance.list') }}" class="metric-link"><div class="metric-card"><div class="metric-label">Today Attendance</div><div class="metric-value">{{ $todayPresent }}</div><div class="metric-help">{{ $todayAbsent }} absent / unmarked</div></div></a></div>
                    @endif
                    @if ($dashboardSettings['hr_monthly_attendance_box'] === 'Enable')
                        <div class="col-lg-3 col-sm-6 col-6"><a href="{{ route('attendance.list') }}" class="metric-link"><div class="metric-card"><div class="metric-label">Monthly Attendance</div><div class="metric-value">{{ $monthlyAttendanceRate }}%</div><div class="metric-help">Based on marked attendance</div></div></a></div>
                    @endif
                    @if ($dashboardSettings['hr_personal_progress_box'] === 'Enable')
                        <div class="col-lg-3 col-sm-6 col-6"    ><a href="{{ route('salary.list') }}" class="metric-link"><div class="metric-card"><div class="metric-label">Payroll Progress</div><div class="metric-value">{{ $paidStaffCount }}/{{ $staffTotal }}</div><div class="metric-help">{{ $pendingPayrollCount }} pending this month</div></div></a></div>
                    @endif
                </div>

                <div class="row g-3 mt-1">
                    @if ($dashboardSettings['hr_7day_attendance_chart'] === 'Enable')
                        <div class="col-lg-7">
                            <div class="panel-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">7 Day Attendance</h5>
                                    <div class="chart-legend">
                                        <span><i class="legend-dot" style="background:#36c396;"></i>Present</span>
                                        <span><i class="legend-dot" style="background:#ff9f43;"></i>Half-day</span>
                                        <span><i class="legend-dot" style="background:#fa6c7c;"></i>Absent</span>
                                    </div>
                                </div>
                                <div class="mini-chart">
                                    @foreach ($attendanceDays as $day)
                                        <div class="chart-month">
                                            <div class="bar-stack">
                                                <span class="bar" style="height: {{ max(2, ($day['present'] / $attendanceMax) * 125) }}px; background:#36c396;"></span>
                                                <span class="bar" style="height: {{ max(2, ($day['half'] / $attendanceMax) * 125) }}px; background:#ff9f43;"></span>
                                                <span class="bar" style="height: {{ max(2, ($day['absent'] / $attendanceMax) * 125) }}px; background:#fa6c7c;"></span>
                                            </div>
                                            <div class="month-label">{{ $day['label'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                    @if ($dashboardSettings['hr_salary_payout_trend_chart'] === 'Enable')
                        <div class="col-lg-5">
                            <div class="panel-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Salary Payout</h5>
                                    <div class="chart-legend">
                                        <span><i class="legend-dot" style="background:#5b6ff0;"></i>Payout</span>
                                        <span><i class="legend-dot" style="background:#36c396;"></i>Paid Staff</span>
                                    </div>
                                </div>
                                <div class="mini-chart">
                                    @foreach ($payrollTrend as $month)
                                        <div class="chart-month">
                                            <div class="bar-stack">
                                                <span class="bar bar-wide" style="height: {{ max(2, ($month['payout'] / $payrollMax) * 125) }}px; background:#5b6ff0;"></span>
                                                <span class="bar" style="height: {{ max(2, ($month['paid_staff'] / max(1, $paidStaffCount)) * 125) }}px; background:#36c396;"></span>
                                            </div>
                                            <div class="month-label">{{ $month['label'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="row g-3 mt-1">
                    @if ($dashboardSettings['hr_payroll_snapshot_table'] === 'Enable')
                        <div class="col-lg-4"><div class="panel-card"><div class="d-flex justify-content-between align-items-center mb-2"><h5 class="card-title mb-0">Payroll</h5><a href="{{ route('payroll.list') }}" class="btn btn-sm btn-primary">View All</a></div><div class="table-responsive mobile-toggle-table"><table class="table mb-0"><thead><tr><th>Summary</th><th class="details-column">Details</th><th>Value</th></tr></thead><tbody><tr><td><div><span class="order-id ms-0">Paid this month</span><div class="collapse mobile-details-collapse d-md-none" id="payroll-paid-month"><div class="mobile-details-row"><span class="mobile-details-label">Value:</span><span class="mobile-details-value">{{ $currencySymbol }} {{ number_format($paidSalaryTotal, 2) }}</span></div></div></div></td><td class="details-column"><a href="#payroll-paid-month" class="toggle-details" data-bs-toggle="collapse"><i class="fas fa-plus-circle" style="color: #ff9f43;"></i></a></td><td class="text-end d-none d-md-table-cell">{{ $currencySymbol }} {{ number_format($paidSalaryTotal, 2) }}</td></tr><tr><td><div><span class="order-id ms-0">Salary pending staff</span><div class="collapse mobile-details-collapse d-md-none" id="payroll-pending-staff"><div class="mobile-details-row"><span class="mobile-details-label">Value:</span><span class="mobile-details-value">{{ $pendingPayrollCount }}</span></div></div></div></td><td class="details-column"><a href="#payroll-pending-staff" class="toggle-details" data-bs-toggle="collapse"><i class="fas fa-plus-circle" style="color: #ff9f43;"></i></a></td><td class="text-end d-none d-md-table-cell">{{ $pendingPayrollCount }}</td></tr></tbody></table></div></div></div>
                    @endif
                    @if ($dashboardSettings['hr_attendance_watch_table'] === 'Enable')
                        <div class="col-lg-4"><div class="panel-card"><div class="d-flex justify-content-between align-items-center mb-2"><h5 class="card-title mb-0">Attendance</h5><a href="{{ route('attendance.list') }}" class="btn btn-sm btn-primary">View All</a></div><div class="table-responsive mobile-toggle-table"><table class="table mb-0"><thead><tr><th>Staff</th><th class="details-column">Details</th><th>Present</th><th>Absent</th></tr></thead><tbody>@forelse ($attendanceWatch as $index => $staff)<tr><td><div><span class="order-id ms-0">{{ $staff['name'] }}</span><div class="collapse mobile-details-collapse d-md-none" id="attendance-watch-{{ $index }}"><div class="mobile-details-row"><span class="mobile-details-label">Present:</span><span class="mobile-details-value">{{ $staff['present'] }}</span></div><div class="mobile-details-row"><span class="mobile-details-label">Absent:</span><span class="mobile-details-value">{{ $staff['absent'] }}</span></div></div></div></td><td class="details-column"><a href="#attendance-watch-{{ $index }}" class="toggle-details" data-bs-toggle="collapse"><i class="fas fa-plus-circle" style="color: #ff9f43;"></i></a></td><td class="d-none d-md-table-cell">{{ $staff['present'] }}</td><td class="d-none d-md-table-cell">{{ $staff['absent'] }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted">No staff found</td></tr>@endforelse</tbody></table></div></div></div>
                    @endif
                    @if ($dashboardSettings['hr_payroll_status_table'] === 'Enable')
                        <div class="col-lg-4"><div class="panel-card"><div class="d-flex justify-content-between align-items-center mb-2"><h5 class="card-title mb-0">Payroll Status</h5><a href="{{ route('payroll.list') }}" class="btn btn-sm btn-primary">View All</a></div><div class="table-responsive mobile-toggle-table"><table class="table mb-0"><thead><tr><th>Staff</th><th class="details-column">Details</th><th>Status</th><th>Amount</th></tr></thead><tbody>@forelse ($currentMonthSalaries->take(4) as $salary)<tr><td><div><span class="order-id ms-0">{{ $salary->staff?->name ?? 'N/A' }}</span><div class="collapse mobile-details-collapse d-md-none" id="payroll-status-{{ $salary->id }}"><div class="mobile-details-row"><span class="mobile-details-label">Status:</span><span class="mobile-details-value">{{ $salary->status ?? 'Pending' }}</span></div><div class="mobile-details-row"><span class="mobile-details-label">Amount:</span><span class="mobile-details-value">{{ $currencySymbol }} {{ number_format($salary->total_salary ?? 0, 2) }}</span></div></div></div></td><td class="details-column"><a href="#payroll-status-{{ $salary->id }}" class="toggle-details" data-bs-toggle="collapse"><i class="fas fa-plus-circle" style="color: #ff9f43;"></i></a></td><td class="d-none d-md-table-cell"><span class="status-pill">{{ $salary->status ?? 'Pending' }}</span></td><td class="d-none d-md-table-cell">{{ $currencySymbol }} {{ number_format($salary->total_salary ?? 0, 2) }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted">No payroll records</td></tr>@endforelse</tbody></table></div></div></div>
                    @endif
                </div>
            </div>
        @endif
    </div>

@endsection

@push('js')
    <script>
        $(document).ready(function() {
            // Toggle details icon animation
            $(document).on('click', '.toggle-details', function() {
                let icon = $(this).find('i');
                if (icon.hasClass('fa-plus-circle')) {
                    icon.removeClass('fa-plus-circle')
                        .addClass('fa-minus-circle')
                        .css('color', 'red');
                } else {
                    icon.removeClass('fa-minus-circle')
                        .addClass('fa-plus-circle')
                        .css('color', '#ff9f43');
                }
            });
        });
    </script>
    <script>
        const currentYear = {{ $currentYear }};
        const previousYear = {{ $previousYear }};
        const salesThisMonth = @json($salesChartThisMonth);
        const purchaseThisMonth = @json($purchaseChartThisMonth);

        const salesChartData = {
            [currentYear]: @json($salesChartthisyear),
            [previousYear]: @json($salesChartpreviousyear),
            'thisMonth': salesThisMonth
        };

        const purchaseChartData = {
            [currentYear]: @json($purchaseChartthisyear),
            [previousYear]: @json($purchaseChartpreviousyear),
            'thisMonth': purchaseThisMonth
        };

        // ✅ Updated chart options
        const options = {
            grid: {
                borderWidth: 1,
                borderColor: 'rgba(67, 87, 133, .09)',
                hoverable: true
            },
            xaxis: {
                ticks: [], // dynamically set below
                tickColor: 'rgba(67, 87, 133, .09)',
                font: {
                    color: '#8e9cad',
                    size: 9
                },
                autoscaleMargin: 0.02
            },
            yaxis: {
                tickColor: 'rgba(67, 87, 133, .09)',
                font: {
                    color: '#8e9cad',
                    size: 10
                },
                tickFormatter: function(val, axis) {
                    return val.toLocaleString();
                }
            },
            legend: {
                show: true,
                position: "nw"
            },
            tooltip: true,
            tooltipOpts: {
                content: function(label, xval, yval, flotItem) {
                    return label + ": " + yval.toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                },
                shifts: {
                    x: 10,
                    y: 20
                },
                defaultTheme: false
            }
        };

        // ✅ Fixed: Tick generator for day labels 1–31
        function getDayTicks(length) {
            return Array.from({
                length
            }, (_, i) => [i, (i + 1).toString()]);
        }

        // Optional: Only show every 2nd or 5th tick (use if labels are still crowded)
        // function getDayTicks(length) {
        //     return Array.from({ length }, (_, i) => [i, (i + 1).toString()])
        //         .filter(([, label]) => parseInt(label) % 2 === 1); // odd days only
        // }

        function renderChart(containerId, label, data, barColor, isMonthly = true) {
            const series = data.map((val, idx) => [idx, val]);

            options.xaxis.ticks = isMonthly ? [
                    [0, 'Jan'],
                    [1, 'Feb'],
                    [2, 'Mar'],
                    [3, 'Apr'],
                    [4, 'May'],
                    [5, 'Jun'],
                    [6, 'Jul'],
                    [7, 'Aug'],
                    [8, 'Sep'],
                    [9, 'Oct'],
                    [10, 'Nov'],
                    [11, 'Dec']
                ] :
                getDayTicks(data.length); // ✅ this ensures 1–31 labels

            $.plot(containerId, [{
                label,
                data: series,
                bars: {
                    show: true,
                    barWidth: 0.3, // ✅ adjusted for tight spacing
                    align: "center",
                    fillColor: barColor
                },
                color: barColor
            }], options);
        }

        // $(document).ready(function () {
        //     renderChart('#saleschart', 'Sales', salesChartData[currentYear], '#44c4fa');
        //     renderChart('#purchasechart', 'Purchases', purchaseChartData[currentYear], '#fa6c7c');

        //     $('#salesYearSelect').on('change', function () {
        //         const year = $(this).val();
        //         if (year === 'month') {
        //             renderChart('#saleschart', 'Sales (This Month)', salesChartData['thisMonth'], '#44c4fa', false);
        //         } else {
        //             renderChart('#saleschart', 'Sales', salesChartData[year], '#44c4fa');
        //         }
        //     });

        //     $('#purchaseYearSelect').on('change', function () {
        //         const year = $(this).val();
        //         if (year === 'month') {
        //             renderChart('#purchasechart', 'Purchases (This Month)', purchaseChartData['thisMonth'], '#fa6c7c', false);
        //         } else {
        //             renderChart('#purchasechart', 'Purchases', purchaseChartData[year], '#fa6c7c');
        //         }
        //     });
        // });

        $(document).ready(function() {
            $('.chart-select2').select2({
                minimumResultsForSearch: -1,
                width: '100%'
            });
            @if ($dashboardSettings['erp_section_enabled'] === 'Enable' && $dashboardSettings['erp_sales_chart'] === 'Enable' && in_array($role, ['inventory-manager', 'sales-manager', 'admin', 'sub-admin']))
                renderChart('#saleschart', 'Sales', salesChartData[currentYear], '#44c4fa');
                $('#salesYearSelect').on('change', function() {
                    const year = $(this).val();
                    if (year === 'month') {
                        renderChart('#saleschart', 'Sales (This Month)', salesChartData['thisMonth'],
                            '#44c4fa', false);
                    } else {
                        renderChart('#saleschart', 'Sales', salesChartData[year], '#44c4fa');
                    }
                });
            @endif

            @if ($dashboardSettings['erp_section_enabled'] === 'Enable' && $dashboardSettings['erp_purchase_chart'] === 'Enable' && in_array($role, ['inventory-manager', 'purchase-manager', 'admin', 'sub-admin']))
                renderChart('#purchasechart', 'Purchases', purchaseChartData[currentYear], '#fa6c7c');
                $('#purchaseYearSelect').on('change', function() {
                    const year = $(this).val();
                    if (year === 'month') {
                        renderChart('#purchasechart', 'Purchases (This Month)', purchaseChartData[
                            'thisMonth'], '#fa6c7c', false);
                    } else {
                        renderChart('#purchasechart', 'Purchases', purchaseChartData[year], '#fa6c7c');
                    }
                });
            @endif

        });
    </script>
    <script>
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
            let url = "{{ url('/api/dashboard-api') }}";

            if (selectedSubAdminId) {
                url += `?selectedSubAdminId=${selectedSubAdminId}`;
            }
            $.ajax({
                url: url,
                method: "GET",
                headers: {
                    "Authorization": "Bearer " + authToken,
                },
                dataType: "json",
                success: function(response) {
                    // console.log("Branch:", response.branch_id);
                    if (response.status) {
                        // console.log(response.data);

                        // Example: Update totals
                        $('#totalPurchase').text(parseFloat(response.data.totals.purchase).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                        $('#totalSales').text(parseFloat(response.data.totals.sales).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                        $('#totalExpense').text(parseFloat(response.data.totals.expense).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                        // ✅ Low Stock Alert
                        const lowStock = response.data.lowStock;
                        if (lowStock && lowStock.threshold > 0 && lowStock.products && lowStock.products.length > 0) {
                            if (typeof window.setTodayLowStockAlerts === 'function') {
                                window.setTodayLowStockAlerts(lowStock);
                            }

                            const sessionKey = 'lowStockAlertShown_' + new Date().toDateString();
                            if (!sessionStorage.getItem(sessionKey)) {
                                sessionStorage.setItem(sessionKey, '1');
                                if (typeof window.openTodayAlertModal === 'function') {
                                    window.openTodayAlertModal('lowstock');
                                }
                                return;

                                const rows = lowStock.products.map(function(p) {
                                    const qty = parseFloat(p.quantity);
                                    const badgeClass = qty <= 0 ? 'bg-lightred' : 'bg-lightyellow';
                                    const label = qty <= 0 ? 'Out of Stock' : 'Low Stock';
                                    return `<tr>
                                        <td style="text-align:left;padding:6px 10px;"><a href="/product-view/${p.id}" style="color:#ff9f43;text-decoration:underline;cursor:pointer;">${p.name}</a></td>
                                        <td style="text-align:center;padding:6px 10px;">${qty.toFixed(3)}</td>
                                        <td style="text-align:center;padding:6px 10px;"><span class="badges ${badgeClass}" style="font-size:11px;">${label}</span></td>
                                    </tr>`;
                                }).join('');

                                Swal.fire({
                                    title: '',
                                    html: `
                                        <div style="font-size:15px;font-weight:700;margin-bottom:10px;color:#333;">⚠️ Low Stock Alert</div>
                                        <p style="margin-bottom:10px;color:#555;font-size:13px;">The following products are below the threshold of <strong>${lowStock.threshold}</strong> units:</p>
                                        <div style="max-height:300px;overflow-y:auto;">
                                            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                                                <thead>
                                                    <tr style="background:#f5f5f5;">
                                                        <th style="text-align:left;padding:8px 10px;border-bottom:1px solid #ddd;">Product</th>
                                                        <th style="text-align:center;padding:8px 10px;border-bottom:1px solid #ddd;">Current Qty</th>
                                                        <th style="text-align:center;padding:8px 10px;border-bottom:1px solid #ddd;">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>${rows}</tbody>
                                            </table>
                                        </div>`,
                                    showConfirmButton: false,
                                    showCancelButton: false,
                                    showCloseButton: true,
                                    width: '600px',
                                });
                            }
                        }
                    }
                },
                error: function(xhr) {
                    // console.error(xhr.responseText);
                }
            });
        });
    </script>

    @if (isset($planWarning))
    <!-- Plan Expiration Warning Modal -->
    <div class="modal fade" id="dashboardPlanWarningModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 12px; border: none; padding: 20px;">
                <div class="modal-header border-0 pb-0" style="justify-content: flex-end;">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 20px; color: #888;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body text-center pt-0">
                    <!-- Warning Icon -->
                    <div style="width: 60px; height: 60px; background-color: #ff5b5c; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <i class="fas fa-exclamation" style="color: white; font-size: 30px;"></i>
                    </div>
                    
                    <h4 class="mb-3" style="font-weight: 700; color: #333;">Plan Expiration Warning</h4>
                    
                    <p class="mb-4 text-muted" style="font-size: 14px;">
                        Your subscription plan will expire in <strong>{{ $planWarning['days_remaining'] }} days</strong>. Please renew it to avoid interruption in service.
                    </p>
                    
                    <div style="background-color: #f8f9fa; border-left: 4px solid #38d39f; border-radius: 8px; padding: 15px; margin-bottom: 20px; text-align: left;">
                        <div class="d-flex justify-content-between mb-2" style="font-size: 13px;">
                            <span class="text-muted">Plan:</span>
                            <strong style="color: #ff5b5c;">{{ $planWarning['name'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2" style="font-size: 13px;">
                            <span class="text-muted">Expires On:</span>
                            <strong style="color: #ff5b5c;">{{ $planWarning['end_date'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between" style="font-size: 13px;">
                            <span class="text-muted">Days Remaining:</span>
                            <strong style="color: #ff5b5c;">{{ $planWarning['days_remaining'] }} days</strong>
                        </div>
                    </div>
                    
                    <div class="text-start">
                        <h6 style="color: #f7933a; font-weight: 700; margin-bottom: 10px;">Contact us to Renew:</h6>
                        <div class="d-flex align-items-center mb-2" style="font-size: 13px; color: #666;">
                            <i class="fas fa-phone-alt me-2"></i> +91 9824734531
                        </div>
                        <div class="d-flex align-items-center" style="font-size: 13px; color: #666;">
                            <i class="fas fa-envelope me-2"></i> info@fableadtechnolabs.com
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        $(document).ready(function() {
            setTimeout(function() {
                $('#dashboardPlanWarningModal').modal('show');
            }, 1000);
        });
    </script>
    @endif
@endpush
