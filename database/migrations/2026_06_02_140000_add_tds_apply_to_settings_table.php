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
        if (!Schema::hasColumn('settings', 'tds_apply')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->boolean('tds_apply')->default(false)->after('send_mail');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('settings', 'tds_apply')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('tds_apply');
            });
        }
    }
};
