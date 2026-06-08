<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'tax_deduction_amount')) {
                $table->decimal('tax_deduction_amount', 12, 2)->nullable()->after('ifsc_code');
            }

            if (!Schema::hasColumn('settings', 'salary_amount_exceeds')) {
                $table->decimal('salary_amount_exceeds', 12, 2)->nullable()->after('tax_deduction_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'salary_amount_exceeds')) {
                $table->dropColumn('salary_amount_exceeds');
            }

            if (Schema::hasColumn('settings', 'tax_deduction_amount')) {
                $table->dropColumn('tax_deduction_amount');
            }
        });
    }
};
