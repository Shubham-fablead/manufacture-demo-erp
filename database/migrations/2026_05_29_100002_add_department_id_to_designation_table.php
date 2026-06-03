<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds the `department_id` column to the `designation` table.
     *
     * The original designation table was created by
     * 2026_05_27_000001_create_hr_module_tables.php but it did NOT include
     * `department_id`, even though:
     *  - DesignationModel->$fillable includes 'department_id'
     *  - DesignationModel->department() declares belongsTo(DepartmentModel, 'department_id')
     *  - UserDetail->designation() chains through this relationship
     */
    public function up(): void
    {
        if (Schema::hasTable('designation') && ! Schema::hasColumn('designation', 'department_id')) {
            Schema::table('designation', function (Blueprint $table) {
                $table->unsignedBigInteger('department_id')
                      ->nullable()
                      ->after('designation_name');

                $table->index('department_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('designation') && Schema::hasColumn('designation', 'department_id')) {
            Schema::table('designation', function (Blueprint $table) {
                $table->dropIndex(['department_id']);
                $table->dropColumn('department_id');
            });
        }
    }
};
