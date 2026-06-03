<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('credit_notes_type', function (Blueprint $table) {
            $table->id();
            $table->string('type_name')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->tinyInteger('isdeleted')->default(0);
            $table->timestamps();
        });

        Schema::create('debit_notes_type', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('create_note_id')->nullable();
            $table->string('invoice_number')->nullable();
            $table->decimal('grand_total', 10, 2)->default(0);
            $table->decimal('remaning_amount', 10, 2)->default(0);
            $table->decimal('total_paid', 10, 2)->default(0);
            $table->decimal('settlement_amount', 10, 2)->default(0);
            $table->text('reason')->nullable();
            $table->decimal('total', 10, 2)->default(0);
            $table->tinyInteger('isDeleted')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debit_notes_type');
        Schema::dropIfExists('credit_notes_type');
    }
};
