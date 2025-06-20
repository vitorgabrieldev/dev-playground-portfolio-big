<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->enum('type', ['credit_card', 'debit_card']);
            $table->string('label')->nullable();
            $table->string('last_digits', 4)->nullable();
            $table->string('brand')->nullable();
            $table->string('holder_name')->nullable();
            $table->string('expiration')->nullable();
            $table->json('bank_data')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->index(['customer_id', 'type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_payment_methods');
    }
}; 