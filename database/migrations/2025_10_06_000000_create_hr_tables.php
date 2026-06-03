<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Attendance
        if (!Schema::hasTable('attendances')) {
            Schema::create('attendances', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->date('date')->nullable();
                $table->string('check_in_time')->nullable();
                $table->string('check_out_time')->nullable();
                $table->string('work_hours')->nullable();
                $table->string('status')->nullable();
                $table->text('reason')->nullable();
                $table->text('description')->nullable();
                $table->boolean('extraday')->default(0);
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // Log attendance (device check-in/out)
        if (!Schema::hasTable('log_attendance')) {
            Schema::create('log_attendance', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->date('check_date')->nullable();
                $table->string('check_in')->nullable();
                $table->string('checkout_out')->nullable();
                $table->timestamps();
            });
        }

        // Salaries
        if (!Schema::hasTable('salaries')) {
            Schema::create('salaries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->unsignedBigInteger('staff_id')->nullable();
                $table->string('month')->nullable();
                $table->string('year')->nullable();
                $table->integer('present')->default(0);
                $table->integer('absent')->default(0);
                $table->integer('extra_present')->default(0);
                $table->decimal('advance_pay', 10, 2)->default(0);
                $table->decimal('salary', 10, 2)->default(0);
                $table->decimal('extra_amount', 10, 2)->default(0);
                $table->decimal('total_salary', 10, 2)->default(0);
                $table->decimal('old_advance_pay', 10, 2)->default(0);
                $table->string('status')->default('pending');
                $table->timestamps();

                $table->foreign('staff_id')->references('id')->on('users')->onDelete('set null');
            });
        }

        // Advance payments
        if (!Schema::hasTable('advance_payments')) {
            Schema::create('advance_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->unsignedBigInteger('staff_id')->nullable();
                $table->decimal('amount', 10, 2)->default(0);
                $table->decimal('paid_amount', 10, 2)->default(0);
                $table->string('status')->default('pending');
                $table->date('date')->nullable();
                $table->string('method')->nullable();
                $table->text('reason')->nullable();
                $table->boolean('isDeleted')->default(0);
                $table->timestamps();

                $table->foreign('staff_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('advance_payments');
        Schema::dropIfExists('salaries');
        Schema::dropIfExists('log_attendance');
        Schema::dropIfExists('attendances');
    }
};
