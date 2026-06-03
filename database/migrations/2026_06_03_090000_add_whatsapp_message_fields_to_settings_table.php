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
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'customer_whatsapp_message')) {
                $table->boolean('customer_whatsapp_message')->default(false)->after('financial_year');
            }

            if (! Schema::hasColumn('settings', 'admin_whatsapp_message')) {
                $table->boolean('admin_whatsapp_message')->default(false)->after('customer_whatsapp_message');
            }

            if (! Schema::hasColumn('settings', 'appointment_reminder_hours_before')) {
                $table->unsignedTinyInteger('appointment_reminder_hours_before')->default(3)->after('admin_whatsapp_message');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'appointment_reminder_hours_before')) {
                $table->dropColumn('appointment_reminder_hours_before');
            }

            if (Schema::hasColumn('settings', 'admin_whatsapp_message')) {
                $table->dropColumn('admin_whatsapp_message');
            }

            if (Schema::hasColumn('settings', 'customer_whatsapp_message')) {
                $table->dropColumn('customer_whatsapp_message');
            }
        });
    }
};
