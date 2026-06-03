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
        if (! Schema::hasTable('department')) {
            // Department table will be created by the HR module tables migration,
            // which already includes these overtime columns. Skip to avoid error.
            return;
        }

        Schema::table('department', function (Blueprint $table) {
            if (! Schema::hasColumn('department', 'enable_overtime')) {
                $table->boolean('enable_overtime')->default(false);
            }
            if (! Schema::hasColumn('department', 'overtime_rate_type')) {
                $table->string('overtime_rate_type')->default('multiplier');
            }
            if (! Schema::hasColumn('department', 'overtime_multiplier')) {
                $table->decimal('overtime_multiplier', 8, 2)->default(1.00);
            }
            if (! Schema::hasColumn('department', 'min_overtime_count_in_minutes')) {
                $table->integer('min_overtime_count_in_minutes')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('department', function (Blueprint $table) {
            $table->dropColumn([
                'enable_overtime',
                'overtime_rate_type',
                'overtime_multiplier',
                'min_overtime_count_in_minutes'
            ]);
        });
    }
};
