<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('user_details')) {
            return;
        }

        $hasDesignationId = Schema::hasColumn('user_details', 'designation_id');
        $hasDepartmentId = Schema::hasColumn('user_details', 'department_id');
        $hasJoiningDate = Schema::hasColumn('user_details', 'joining_date');
        $hasWorkingLocation = Schema::hasColumn('user_details', 'working_location');
        $hasSalary = Schema::hasColumn('user_details', 'salary');
        $hasFacePhoto = Schema::hasColumn('user_details', 'face_photo');

        Schema::table('user_details', function (Blueprint $table) use (
            $hasDesignationId,
            $hasDepartmentId,
            $hasJoiningDate,
            $hasWorkingLocation,
            $hasSalary,
            $hasFacePhoto
        ) {
            if (!$hasDesignationId) {
                $table->unsignedBigInteger('designation_id')->nullable()->after('user_id');
            }

            if (!$hasDepartmentId) {
                $table->unsignedBigInteger('department_id')->nullable()->after('designation_id');
            }

            if (!$hasJoiningDate) {
                $table->date('joining_date')->nullable()->after('country');
            }

            if (!$hasWorkingLocation) {
                $table->string('working_location')->nullable()->after('joining_date');
            }

            if (!$hasSalary) {
                $table->decimal('salary', 12, 2)->nullable()->after('working_location');
            }

            if (!$hasFacePhoto) {
                $table->string('face_photo')->nullable()->after('salary');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('user_details')) {
            return;
        }

        $columns = [];

        if (Schema::hasColumn('user_details', 'designation_id')) {
            $columns[] = 'designation_id';
        }
        if (Schema::hasColumn('user_details', 'department_id')) {
            $columns[] = 'department_id';
        }
        if (Schema::hasColumn('user_details', 'joining_date')) {
            $columns[] = 'joining_date';
        }
        if (Schema::hasColumn('user_details', 'working_location')) {
            $columns[] = 'working_location';
        }
        if (Schema::hasColumn('user_details', 'salary')) {
            $columns[] = 'salary';
        }
        if (Schema::hasColumn('user_details', 'face_photo')) {
            $columns[] = 'face_photo';
        }

        if (!empty($columns)) {
            Schema::table('user_details', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
