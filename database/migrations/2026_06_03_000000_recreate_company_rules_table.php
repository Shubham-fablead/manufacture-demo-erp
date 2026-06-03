<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('company_rules');
        
        Schema::create('company_rules', function (Blueprint $table) {
            $table->id();
            $table->boolean('enable_payroll')->default(1);
            $table->string('payroll_type')->default('monthly');
            $table->decimal('working_hours_per_day', 8, 2)->default(8.00);
            $table->boolean('include_holidays_in_working_days')->default(0);
            $table->decimal('half_day_hours', 8, 2)->default(4.00);
            $table->boolean('sunday_off')->default(1);
            $table->string('sunday_pay_type')->default('unpaid');
            $table->boolean('saturday_off_enabled')->default(0);
            $table->string('saturday_off_type')->default('all');
            $table->string('saturday_off_pattern')->nullable();
            $table->boolean('saturday_half_day_enabled')->default(0);
            $table->string('saturday_half_day_pattern')->nullable();
            $table->string('saturday_pay_type')->default('unpaid');
            $table->integer('yearly_holidays')->default(0);
            $table->boolean('enable_tax')->default(0);
            $table->string('tax_type')->default('percentage');
            $table->decimal('salary_above_tax', 12, 2)->default(12000.00);
            $table->decimal('tax', 8, 2)->default(0.00);
            $table->string('lunch_break')->nullable();
            $table->time('start_time')->nullable();
            $table->time('half_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('grace_period')->default(15);
            $table->boolean('enable_overtime')->default(0);
            $table->string('overtime_rate_type')->default('multiplier');
            $table->decimal('overtime_multiplier', 8, 2)->default(1.00);
            $table->integer('min_overtime_count_in_minutes')->default(0);
            $table->boolean('enable_pf')->default(0);
            $table->decimal('employee_pf', 8, 2)->default(0);
            $table->decimal('employer_pf', 8, 2)->default(0);
            $table->boolean('enable_esi')->default(0);
            $table->decimal('employee_esi', 8, 2)->default(0);
            $table->decimal('employer_esi', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_rules');
    }
};
