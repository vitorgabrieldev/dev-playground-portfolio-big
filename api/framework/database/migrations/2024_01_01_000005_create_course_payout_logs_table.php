<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_payout_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('producer_id');
            $table->enum('type', ['release', 'withdraw', 'refund', 'adjustment']);
            $table->unsignedDecimal('amount', 12, 2);
            $table->unsignedBigInteger('payout_method_id')->nullable();
            $table->string('description')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('related_type')->nullable();
            $table->timestamp('logged_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('producer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('payout_method_id')->references('id')->on('customer_payout_methods')->onDelete('set null');
            $table->index(['producer_id', 'type'], 'payout_logs_producer_type_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_payout_logs');
    }
}; 