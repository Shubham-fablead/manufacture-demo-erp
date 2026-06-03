<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('attendance')) {
            return;
        }

        $hasCheckInLatitude = Schema::hasColumn('attendance', 'check_in_latitude');
        $hasCheckInLongitude = Schema::hasColumn('attendance', 'check_in_longitude');
        $hasCheckInLocationName = Schema::hasColumn('attendance', 'check_in_location_name');
        $hasCheckOutLatitude = Schema::hasColumn('attendance', 'check_out_latitude');
        $hasCheckOutLongitude = Schema::hasColumn('attendance', 'check_out_longitude');
        $hasCheckOutLocationName = Schema::hasColumn('attendance', 'check_out_location_name');

        Schema::table('attendance', function (Blueprint $table) use (
            $hasCheckInLatitude,
            $hasCheckInLongitude,
            $hasCheckInLocationName,
            $hasCheckOutLatitude,
            $hasCheckOutLongitude,
            $hasCheckOutLocationName
        ) {
            if (!$hasCheckInLatitude) {
                $table->decimal('check_in_latitude', 10, 7)->nullable()->after('check_in_time');
            }
            if (!$hasCheckInLongitude) {
                $table->decimal('check_in_longitude', 10, 7)->nullable()->after('check_in_latitude');
            }
            if (!$hasCheckInLocationName) {
                $table->string('check_in_location_name', 255)->nullable()->after('check_in_longitude');
            }
            if (!$hasCheckOutLatitude) {
                $table->decimal('check_out_latitude', 10, 7)->nullable()->after('check_out_time');
            }
            if (!$hasCheckOutLongitude) {
                $table->decimal('check_out_longitude', 10, 7)->nullable()->after('check_out_latitude');
            }
            if (!$hasCheckOutLocationName) {
                $table->string('check_out_location_name', 255)->nullable()->after('check_out_longitude');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('attendance')) {
            return;
        }

        $columns = [];
        foreach ([
            'check_in_latitude',
            'check_in_longitude',
            'check_in_location_name',
            'check_out_latitude',
            'check_out_longitude',
            'check_out_location_name',
        ] as $column) {
            if (Schema::hasColumn('attendance', $column)) {
                $columns[] = $column;
            }
        }

        if ($columns !== []) {
            Schema::table('attendance', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
