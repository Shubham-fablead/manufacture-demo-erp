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
            $table->string('cin_no')->nullable()->after('gst_num');
            $table->decimal('office_latitude', 10, 7)->nullable()->after('cin_no');
            $table->decimal('office_longitude', 10, 7)->nullable()->after('office_latitude');
            $table->integer('office_radius')->nullable()->default(200)->after('office_longitude');
            $table->decimal('overtime_after_hours', 5, 2)->nullable()->after('office_radius');
            $table->tinyInteger('location_check_enabled')->default(0)->after('overtime_after_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'cin_no',
                'office_latitude',
                'office_longitude',
                'office_radius',
                'overtime_after_hours',
                'location_check_enabled'
            ]);
        });
    }
};
