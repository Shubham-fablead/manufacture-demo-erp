<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * The 'leaves' table was created with 'from_date' / 'to_date' columns,
     * but all controllers and models expect 'start_date' / 'end_date'.
     * This migration adds the expected columns so the HR module works correctly.
     */
    public function up(): void
    {
        if (Schema::hasTable('leaves')) {
            Schema::table('leaves', function (Blueprint $table) {
                if (! Schema::hasColumn('leaves', 'start_date')) {
                    $table->date('start_date')->nullable()->after('leave_type_id');
                }
                if (! Schema::hasColumn('leaves', 'end_date')) {
                    $table->date('end_date')->nullable()->after('start_date');
                }
            });

            // Copy existing from_date / to_date values into the new columns
            DB::statement('UPDATE leaves SET start_date = from_date WHERE start_date IS NULL AND from_date IS NOT NULL');
            DB::statement('UPDATE leaves SET end_date = to_date WHERE end_date IS NULL AND to_date IS NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('leaves')) {
            Schema::table('leaves', function (Blueprint $table) {
                $table->dropColumn(['start_date', 'end_date']);
            });
        }
    }
};
