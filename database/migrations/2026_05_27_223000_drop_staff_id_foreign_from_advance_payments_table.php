<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advance_payments', function (Blueprint $table) {
            $table->dropForeign('advance_payments_staff_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::table('advance_payments', function (Blueprint $table) {
            $table->foreign('staff_id', 'advance_payments_staff_id_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }
};
