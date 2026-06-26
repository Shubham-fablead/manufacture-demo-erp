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

        if (!Schema::hasColumn('user_details', 'delivery_address')) {
            Schema::table('user_details', function (Blueprint $table) {
                $table->text('delivery_address')->nullable()->after('address');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('user_details')) {
            return;
        }

        if (Schema::hasColumn('user_details', 'delivery_address')) {
            Schema::table('user_details', function (Blueprint $table) {
                $table->dropColumn('delivery_address');
            });
        }
    }
};
