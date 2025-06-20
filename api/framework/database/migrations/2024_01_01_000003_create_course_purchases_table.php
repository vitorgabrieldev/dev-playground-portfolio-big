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
        // Tabela de compras de cursos
        Schema::create('course_purchases', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedDecimal('price_paid', 10, 2);
            $table->unsignedDecimal('platform_fee', 10, 2);
            $table->unsignedDecimal('creator_revenue', 10, 2);
            $table->enum('payment_status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->enum('release_status', ['blocked', 'available', 'withdrawn'])->default('blocked');
            $table->timestamp('purchased_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->unsignedBigInteger('payment_method_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('payment_method_id')->references('id')->on('customer_payment_methods')->onDelete('set null');
            $table->unique(['customer_id', 'course_id', 'deleted_at'], 'customer_course_purchase_unique');
            $table->index(['payment_status', 'release_status', 'purchased_at'], 'purchases_status_release_purchased_index');
            $table->index(['course_id', 'payment_status'], 'purchases_course_payment_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_purchases');
    }
}; 