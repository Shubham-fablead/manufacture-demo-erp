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

        $hasShiftTime = Schema::hasColumn('user_details', 'shift_time');

        Schema::table('user_details', function (Blueprint $table) use ($hasShiftTime) {
            if (!$hasShiftTime) {
                $table->string('shift_time')->nullable()->after('working_location');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('user_details')) {
            return;
        }

        Schema::table('user_details', function (Blueprint $table) {
            if (Schema::hasColumn('user_details', 'shift_time')) {
                $table->dropColumn('shift_time');
            }
        });
    }
};
