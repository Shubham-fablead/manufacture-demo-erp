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

        $hasCheckInIp = Schema::hasColumn('attendance', 'check_in_ip_address');
        $hasCheckOutIp = Schema::hasColumn('attendance', 'check_out_ip_address');

        Schema::table('attendance', function (Blueprint $table) use ($hasCheckInIp, $hasCheckOutIp) {
            if (!$hasCheckInIp) {
                $table->string('check_in_ip_address', 45)->nullable()->after('check_in_location_name');
            }
            if (!$hasCheckOutIp) {
                $table->string('check_out_ip_address', 45)->nullable()->after('check_out_location_name');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('attendance')) {
            return;
        }

        $columns = [];
        if (Schema::hasColumn('attendance', 'check_in_ip_address')) {
            $columns[] = 'check_in_ip_address';
        }
        if (Schema::hasColumn('attendance', 'check_out_ip_address')) {
            $columns[] = 'check_out_ip_address';
        }

        if ($columns !== []) {
            Schema::table('attendance', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
