<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expense_types', function (Blueprint $table) {
            if (! Schema::hasColumn('expense_types', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('id');
                $table->index('branch_id');
            }

            if (! Schema::hasColumn('expense_types', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('isDeleted');
                $table->index('created_by');
            }
        });

        Schema::table('expenses', function (Blueprint $table) {
            if (! Schema::hasColumn('expenses', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('id');
                $table->index('branch_id');
            }

            if (! Schema::hasColumn('expenses', 'sr_no')) {
                $table->unsignedBigInteger('sr_no')->nullable()->after('branch_id');
                $table->index(['branch_id', 'sr_no']);
            }

            if (! Schema::hasColumn('expenses', 'payment_mode')) {
                $table->string('payment_mode')->nullable()->after('sr_no');
            }

            if (! Schema::hasColumn('expenses', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('isDeleted');
                $table->index('created_by');
            }

            if (Schema::hasColumn('expenses', 'expense_type_id')) {
                $table->index('expense_type_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'created_by')) {
                $table->dropIndex(['created_by']);
                $table->dropColumn('created_by');
            }

            if (Schema::hasColumn('expenses', 'payment_mode')) {
                $table->dropColumn('payment_mode');
            }

            if (Schema::hasColumn('expenses', 'sr_no')) {
                $table->dropIndex(['branch_id', 'sr_no']);
                $table->dropColumn('sr_no');
            }

            if (Schema::hasColumn('expenses', 'branch_id')) {
                $table->dropIndex(['branch_id']);
                $table->dropColumn('branch_id');
            }
        });

        Schema::table('expense_types', function (Blueprint $table) {
            if (Schema::hasColumn('expense_types', 'created_by')) {
                $table->dropIndex(['created_by']);
                $table->dropColumn('created_by');
            }

            if (Schema::hasColumn('expense_types', 'branch_id')) {
                $table->dropIndex(['branch_id']);
                $table->dropColumn('branch_id');
            }
        });
    }
};
