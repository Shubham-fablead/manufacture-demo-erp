<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_store', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_store', 'custom_invoice_id')) {
                $table->unsignedBigInteger('custom_invoice_id')->nullable()->after('order_id');
                $table->index('custom_invoice_id');
            }

            if (! Schema::hasColumn('payment_store', 'purchase_id')) {
                $table->unsignedBigInteger('purchase_id')->nullable()->after('custom_invoice_id');
                $table->index('purchase_id');
            }

            if (! Schema::hasColumn('payment_store', 'status')) {
                $table->string('status')->nullable()->after('remaining_amount');
                $table->index('status');
            }

            if (! Schema::hasColumn('payment_store', 'remarks')) {
                $table->text('remarks')->nullable()->after('status');
            }

            if (! Schema::hasColumn('payment_store', 'bank_id')) {
                $table->unsignedBigInteger('bank_id')->nullable()->after('remarks');
                $table->index('bank_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_store', function (Blueprint $table) {
            if (Schema::hasColumn('payment_store', 'bank_id')) {
                $table->dropIndex(['bank_id']);
                $table->dropColumn('bank_id');
            }

            if (Schema::hasColumn('payment_store', 'remarks')) {
                $table->dropColumn('remarks');
            }

            if (Schema::hasColumn('payment_store', 'status')) {
                $table->dropIndex(['status']);
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('payment_store', 'purchase_id')) {
                $table->dropIndex(['purchase_id']);
                $table->dropColumn('purchase_id');
            }

            if (Schema::hasColumn('payment_store', 'custom_invoice_id')) {
                $table->dropIndex(['custom_invoice_id']);
                $table->dropColumn('custom_invoice_id');
            }
        });
    }
};
