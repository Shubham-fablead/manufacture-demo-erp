<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates all HR module tables required by the HR controllers:
     * department, designation, attendance (raw table), company_rules,
     * leave_type, leave, payroll, holiday_calendar
     */
    public function up(): void
    {
        // Department
        if (! Schema::hasTable('department')) {
            Schema::create('department', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->string('department_name');
                $table->text('description')->nullable();
                $table->boolean('enable_overtime')->default(0);
                $table->string('overtime_rate_type')->default('multiplier');
                $table->decimal('overtime_multiplier', 8, 2)->default(1);
                $table->integer('min_overtime_count_in_minutes')->default(0);
                $table->timestamps();
            });
        }

        // Designation
        if (! Schema::hasTable('designation')) {
            Schema::create('designation', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->string('designation_name');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // Attendance (raw table used by AttendanceeController — distinct from 'attendances')
        if (! Schema::hasTable('attendance')) {
            Schema::create('attendance', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->date('date')->nullable();
                $table->string('check_in_time')->nullable();
                $table->string('check_out_time')->nullable();
                $table->string('work_hours')->nullable();
                $table->string('overtime')->nullable();
                $table->string('status')->nullable();  // P, A, H, HP, L, etc.
                $table->text('reason')->nullable();
                $table->text('description')->nullable();
                $table->boolean('extraday')->default(0);
                $table->string('location')->nullable();
                $table->string('check_in_ip')->nullable();
                $table->string('check_out_ip')->nullable();
                $table->boolean('is_late')->default(0);
                $table->string('late_duration')->nullable();
                $table->timestamps();
            });
        }

        // Company Rules
        if (! Schema::hasTable('company_rules')) {
            Schema::create('company_rules', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->time('office_start_time')->nullable();
                $table->time('office_end_time')->nullable();
                $table->integer('working_days_per_month')->default(26);
                $table->integer('working_hours_per_day')->default(8);
                $table->integer('late_grace_period_minutes')->default(15);
                $table->decimal('late_deduction_per_minute', 8, 4)->default(0);
                $table->decimal('tax', 5, 2)->default(0);
                $table->decimal('salary_above_tax', 12, 2)->default(0);
                $table->boolean('enable_face_recognition')->default(0);
                $table->integer('shift_start_hour')->nullable();
                $table->integer('shift_start_minute')->nullable();
                $table->integer('shift_end_hour')->nullable();
                $table->integer('shift_end_minute')->nullable();
                $table->timestamps();
            });
        }

        // Leave Type
        if (! Schema::hasTable('leave_type')) {
            Schema::create('leave_type', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->string('leave_type');
                $table->integer('number_of_leaves')->default(0);
                $table->boolean('allow_half_day')->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // Leave
        if (! Schema::hasTable('leave')) {
            Schema::create('leave', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('leave_type_id')->nullable();
                $table->date('from_date')->nullable();
                $table->date('to_date')->nullable();
                $table->integer('no_of_days')->default(1);
                $table->string('status')->default('pending'); // pending, approved, rejected
                $table->text('reason')->nullable();
                $table->text('remarks')->nullable();
                $table->boolean('half_day')->default(0);
                $table->timestamps();
            });
        }

        // Payroll
        if (! Schema::hasTable('payroll')) {
            Schema::create('payroll', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('leave_type')->nullable();
                $table->string('month_year')->nullable();   // e.g. 2025-10
                $table->decimal('salary_amount', 12, 2)->default(0);
                $table->decimal('total_leaves', 8, 2)->default(0);
                $table->decimal('total_half_day', 8, 2)->default(0);
                $table->decimal('total_paid_leaves', 8, 2)->default(0);
                $table->decimal('used_paid_leaves', 8, 2)->default(0);
                $table->decimal('remaining_paid_leaves', 8, 2)->default(0);
                $table->decimal('salary_deduction', 12, 2)->default(0);
                $table->decimal('tax_deduction', 12, 2)->default(0);
                $table->decimal('bonuses', 12, 2)->default(0);  // used for advance deduction
                $table->decimal('net_salary', 12, 2)->default(0);
                $table->decimal('worked_hours', 8, 2)->default(0);
                $table->decimal('overtime_pay', 12, 2)->default(0);
                $table->decimal('total_overtime_hours', 8, 2)->default(0);
                $table->string('acc_number')->nullable();
                $table->string('bank_name')->nullable();
                $table->string('ifsc_code')->nullable();
                $table->string('acc_in_name')->nullable();
                $table->string('branch_name')->nullable();
                $table->string('branch_code')->nullable();
                $table->date('payment_date')->nullable();
                $table->string('payment_status')->default('paid');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }

        // Holiday Calendar
        if (! Schema::hasTable('holiday_calendar')) {
            Schema::create('holiday_calendar', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->string('holiday_name');
                $table->date('date');
                $table->string('type')->default('national'); // national, optional, company
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holiday_calendar');
        Schema::dropIfExists('payroll');
        Schema::dropIfExists('leave');
        Schema::dropIfExists('leave_type');
        Schema::dropIfExists('company_rules');
        Schema::dropIfExists('attendance');
        Schema::dropIfExists('designation');
        Schema::dropIfExists('department');
    }
};
