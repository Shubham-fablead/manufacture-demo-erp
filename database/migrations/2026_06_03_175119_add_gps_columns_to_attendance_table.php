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
        Schema::table('attendance', function (Blueprint $table) {
            $table->string('check_in_latitude')->nullable();
            $table->string('check_in_longitude')->nullable();
            $table->string('check_in_location_name')->nullable();
            $table->string('check_out_latitude')->nullable();
            $table->string('check_out_longitude')->nullable();
            $table->string('check_out_location_name')->nullable();
            $table->string('checkin_method')->default('manual')->nullable();
            $table->integer('late_minutes')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            $table->dropColumn([
                'check_in_latitude',
                'check_in_longitude',
                'check_in_location_name',
                'check_out_latitude',
                'check_out_longitude',
                'check_out_location_name',
                'checkin_method',
                'late_minutes'
            ]);
        });
    }
};
