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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'shipping')) {
                $table->decimal('shipping', 15, 2)->default(0)->after('order_number');
            }

            if (!Schema::hasColumn('orders', 'tds_percentage')) {
                $table->decimal('tds_percentage', 5, 2)->default(0)->after('shipping');
            }

            if (!Schema::hasColumn('orders', 'tds_amount')) {
                $table->decimal('tds_amount', 15, 2)->default(0)->after('tds_percentage');
            }

            if (!Schema::hasColumn('orders', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('gst_option');
            }

            if (!Schema::hasColumn('orders', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('branch_id');
            }

            if (!Schema::hasColumn('orders', 'remaining_amount')) {
                $table->decimal('remaining_amount', 15, 2)->default(0)->after('total_amount');
            }

            if (!Schema::hasColumn('orders', 'delivery_status')) {
                $table->string('delivery_status')->default('pending')->after('payment_status');
            }

            if (!Schema::hasColumn('orders', 'quotation_status')) {
                $table->string('quotation_status')->default('sales')->after('order_invoice');
            }

            if (!Schema::hasColumn('orders', 'approved_status')) {
                $table->string('approved_status')->nullable()->after('quotation_status');
            }

            if (!Schema::hasColumn('orders', 'remarks')) {
                $table->text('remarks')->nullable()->after('approved_status');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'product_gst_details')) {
                $table->json('product_gst_details')->nullable()->after('product_id');
            }

            if (!Schema::hasColumn('order_items', 'product_gst_total')) {
                $table->decimal('product_gst_total', 15, 2)->default(0)->after('product_gst_details');
            }

            if (!Schema::hasColumn('order_items', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('total_amount');
            }

            if (!Schema::hasColumn('order_items', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('branch_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $columns = [
                'product_gst_details',
                'product_gst_total',
                'branch_id',
                'created_by',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('order_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            $columns = [
                'shipping',
                'tds_percentage',
                'tds_amount',
                'branch_id',
                'created_by',
                'remaining_amount',
                'delivery_status',
                'quotation_status',
                'approved_status',
                'remarks',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
