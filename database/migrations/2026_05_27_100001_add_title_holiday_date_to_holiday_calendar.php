<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The holiday_calendar table was created with 'holiday_name' and 'date' columns,
     * but all controllers query for 'title' and 'holiday_date'.
     * This migration adds the expected columns and copies existing data.
     */
    public function up(): void
    {
        if (Schema::hasTable('holiday_calendar')) {
            Schema::table('holiday_calendar', function (Blueprint $table) {
                if (! Schema::hasColumn('holiday_calendar', 'title')) {
                    $table->string('title')->nullable()->after('branch_id');
                }
                if (! Schema::hasColumn('holiday_calendar', 'holiday_date')) {
                    $table->date('holiday_date')->nullable()->after('title');
                }
            });

            // Copy existing data to the new column names
            DB::statement('UPDATE holiday_calendar SET title = holiday_name WHERE title IS NULL AND holiday_name IS NOT NULL');
            DB::statement('UPDATE holiday_calendar SET holiday_date = `date` WHERE holiday_date IS NULL AND `date` IS NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('holiday_calendar')) {
            Schema::table('holiday_calendar', function (Blueprint $table) {
                $table->dropColumn(['title', 'holiday_date']);
            });
        }
    }
};
