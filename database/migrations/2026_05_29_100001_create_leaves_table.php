<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the `leaves` table required by LeaveModel ($table = 'leaves').
     *
     * NOTE: The HR migration (2026_05_27_000001_create_hr_module_tables) already created a
     * `leave` table (no "s"). LeaveModel explicitly declares $table = 'leaves', which is a
     * different table name. This migration creates the correctly-named `leaves` table.
     *
     * Relationships covered:
     *  - LeaveModel->user()         FK: user_id → users.id
     *  - LeaveModel->leaveType()    FK: leave_id → leave_type.id  (bound to leave_id in model)
     *  - LeaveModel->creator()      FK: created_by → users.id
     *
     * $fillable includes: firstname, user_id, start_date, end_date, from_date, to_date,
     *                     no_of_day, no_of_days, reason, leave_id, leave_type_id,
     *                     created_by, status
     */
    public function up(): void
    {
        if (! Schema::hasTable('leaves')) {
            Schema::create('leaves', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_id')->nullable();

                // Staff member taking the leave
                $table->unsignedBigInteger('user_id')->nullable();

                // Leave type foreign keys (model uses both leave_id and leave_type_id)
                $table->unsignedBigInteger('leave_type_id')->nullable();
                $table->unsignedBigInteger('leave_id')->nullable(); // alias FK in leaveType() relationship

                // Date range columns
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->date('from_date')->nullable();   // legacy column
                $table->date('to_date')->nullable();     // legacy column

                // Day count columns
                $table->integer('no_of_day')->default(0);
                $table->integer('no_of_days')->default(0);

                // Applicant name (denormalised)
                $table->string('firstname')->nullable();

                // Reason & approval
                $table->text('reason')->nullable();
                $table->string('status')->default('pending'); // pending, approved, rejected

                // Audit
                $table->unsignedBigInteger('created_by')->nullable();

                $table->timestamps();

                // Indexes
                $table->index(['user_id', 'status']);
                $table->index(['branch_id', 'start_date', 'end_date']);
                $table->index('leave_type_id');
                $table->index('leave_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
