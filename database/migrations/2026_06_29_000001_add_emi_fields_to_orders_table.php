<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'emi_down_payment')) {
                $table->decimal('emi_down_payment', 15, 2)->nullable()->after('payment_method');
            }

            if (!Schema::hasColumn('orders', 'emi_loan_amount')) {
                $table->decimal('emi_loan_amount', 15, 2)->nullable()->after('emi_down_payment');
            }

            if (!Schema::hasColumn('orders', 'emi_interest_rate')) {
                $table->decimal('emi_interest_rate', 8, 2)->nullable()->after('emi_loan_amount');
            }

            if (!Schema::hasColumn('orders', 'emi_tenure')) {
                $table->unsignedInteger('emi_tenure')->nullable()->after('emi_interest_rate');
            }

            if (!Schema::hasColumn('orders', 'emi_monthly_amount')) {
                $table->decimal('emi_monthly_amount', 15, 2)->nullable()->after('emi_tenure');
            }

            if (!Schema::hasColumn('orders', 'emi_aadhar_number')) {
                $table->string('emi_aadhar_number', 20)->nullable()->after('emi_monthly_amount');
            }

            if (!Schema::hasColumn('orders', 'emi_pan_number')) {
                $table->string('emi_pan_number', 20)->nullable()->after('emi_aadhar_number');
            }

            if (!Schema::hasColumn('orders', 'emi_guarnator_name')) {
                $table->string('emi_guarnator_name')->nullable()->after('emi_pan_number');
            }

            if (!Schema::hasColumn('orders', 'emi_do_id')) {
                $table->unsignedBigInteger('emi_do_id')->nullable()->after('emi_guarnator_name');
            }

            if (!Schema::hasColumn('orders', 'emi_bank_id')) {
                $table->unsignedBigInteger('emi_bank_id')->nullable()->after('emi_do_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $columns = [
                'emi_down_payment',
                'emi_loan_amount',
                'emi_interest_rate',
                'emi_tenure',
                'emi_monthly_amount',
                'emi_aadhar_number',
                'emi_pan_number',
                'emi_guarnator_name',
                'emi_do_id',
                'emi_bank_id',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
