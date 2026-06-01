<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Main dashboard sections
            $table->string('crm_section_enabled')->default('Enable')->nullable()->after('send_mail');
            $table->string('hr_section_enabled')->default('Enable')->nullable()->after('crm_section_enabled');
            $table->string('erp_section_enabled')->default('Enable')->nullable()->after('hr_section_enabled');

            // CRM Dashboard Subsections
            $table->string('crm_lead_pipeline_box')->default('Enable')->nullable()->after('erp_section_enabled');
            $table->string('crm_conversion_box')->default('Enable')->nullable()->after('crm_lead_pipeline_box');
            $table->string('crm_followup_lead_box')->default('Enable')->nullable()->after('crm_conversion_box');
            $table->string('crm_meeting_momentum_box')->default('Enable')->nullable()->after('crm_followup_lead_box');
            $table->string('crm_lead_status_mix_chart')->default('Enable')->nullable()->after('crm_meeting_momentum_box');
            $table->string('crm_activity_trend_chart')->default('Enable')->nullable()->after('crm_lead_status_mix_chart');
            $table->string('crm_pipeline_quality_table')->default('Enable')->nullable()->after('crm_activity_trend_chart');
            $table->string('crm_recent_leads_table')->default('Enable')->nullable()->after('crm_pipeline_quality_table');
            $table->string('crm_next_7_days_table')->default('Enable')->nullable()->after('crm_recent_leads_table');

            // HR Dashboard Subsections
            $table->string('hr_staff_strength_box')->default('Enable')->nullable()->after('crm_next_7_days_table');
            $table->string('hr_active_staff_box')->default('Enable')->nullable()->after('hr_staff_strength_box');
            $table->string('hr_monthly_attendance_box')->default('Enable')->nullable()->after('hr_active_staff_box');
            $table->string('hr_personal_progress_box')->default('Enable')->nullable()->after('hr_monthly_attendance_box');
            $table->string('hr_7day_attendance_chart')->default('Enable')->nullable()->after('hr_personal_progress_box');
            $table->string('hr_salary_payout_trend_chart')->default('Enable')->nullable()->after('hr_7day_attendance_chart');
            $table->string('hr_payroll_snapshot_table')->default('Enable')->nullable()->after('hr_salary_payout_trend_chart');
            $table->string('hr_attendance_watch_table')->default('Enable')->nullable()->after('hr_payroll_snapshot_table');
            $table->string('hr_payroll_status_table')->default('Enable')->nullable()->after('hr_attendance_watch_table');

            // ERP Dashboard Subsections
            $table->string('erp_total_sales_box')->default('Enable')->nullable()->after('hr_payroll_status_table');
            $table->string('erp_total_purchase_box')->default('Enable')->nullable()->after('erp_total_sales_box');
            $table->string('erp_total_expense_box')->default('Enable')->nullable()->after('erp_total_purchase_box');
            $table->string('erp_sales_invoice_count_box')->default('Enable')->nullable()->after('erp_total_expense_box');
            $table->string('erp_purchase_invoice_count_box')->default('Enable')->nullable()->after('erp_sales_invoice_count_box');
            $table->string('erp_customers_count_box')->default('Enable')->nullable()->after('erp_purchase_invoice_count_box');
            $table->string('erp_vendors_count_box')->default('Enable')->nullable()->after('erp_customers_count_box');
            $table->string('erp_sales_chart')->default('Enable')->nullable()->after('erp_vendors_count_box');
            $table->string('erp_purchase_chart')->default('Enable')->nullable()->after('erp_sales_chart');
            $table->string('erp_latest_sales_table')->default('Enable')->nullable()->after('erp_purchase_chart');
            $table->string('erp_latest_purchases_table')->default('Enable')->nullable()->after('erp_latest_sales_table');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Main sections
            $table->dropColumn(['crm_section_enabled', 'hr_section_enabled', 'erp_section_enabled']);

            // CRM subsections
            $table->dropColumn([
                'crm_lead_pipeline_box', 'crm_conversion_box', 'crm_followup_lead_box',
                'crm_meeting_momentum_box', 'crm_lead_status_mix_chart', 'crm_activity_trend_chart',
                'crm_pipeline_quality_table', 'crm_recent_leads_table', 'crm_next_7_days_table'
            ]);

            // HR subsections
            $table->dropColumn([
                'hr_staff_strength_box', 'hr_active_staff_box', 'hr_monthly_attendance_box',
                'hr_personal_progress_box', 'hr_7day_attendance_chart', 'hr_salary_payout_trend_chart',
                'hr_payroll_snapshot_table', 'hr_attendance_watch_table', 'hr_payroll_status_table'
            ]);

            // ERP subsections
            $table->dropColumn([
                'erp_total_sales_box', 'erp_total_purchase_box', 'erp_total_expense_box',
                'erp_sales_invoice_count_box', 'erp_purchase_invoice_count_box',
                'erp_customers_count_box', 'erp_vendors_count_box',
                'erp_sales_chart', 'erp_purchase_chart',
                'erp_latest_sales_table', 'erp_latest_purchases_table'
            ]);
        });
    }
};
