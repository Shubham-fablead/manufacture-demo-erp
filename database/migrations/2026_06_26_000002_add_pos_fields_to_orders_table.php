<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'order_date')) {
                $table->date('order_date')->nullable()->after('order_number');
            }

            if (!Schema::hasColumn('orders', 'assigned_staff')) {
                $table->unsignedBigInteger('assigned_staff')->nullable()->after('user_id');
            }

            if (!Schema::hasColumn('orders', 'order_type')) {
                $table->string('order_type')->nullable()->after('assigned_staff');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $columns = ['order_date', 'assigned_staff', 'order_type'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
