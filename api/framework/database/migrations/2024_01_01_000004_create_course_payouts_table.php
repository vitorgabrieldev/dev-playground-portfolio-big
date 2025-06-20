<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_payouts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('producer_id');
            $table->unsignedDecimal('amount', 12, 2);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('payout_method_id')->nullable();
            $table->string('payout_method')->nullable();
            $table->json('payout_data')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('producer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('payout_method_id')->references('id')->on('customer_payout_methods')->onDelete('set null');
            $table->index(['producer_id', 'status'], 'payouts_producer_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_payouts');
    }
}; 