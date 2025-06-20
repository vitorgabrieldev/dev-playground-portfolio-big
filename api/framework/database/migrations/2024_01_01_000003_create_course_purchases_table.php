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
        Schema::create('course_purchases', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id'); // Comprador
            $table->unsignedBigInteger('course_id');
            $table->unsignedDecimal('price_paid', 10, 2);
            $table->unsignedDecimal('platform_fee', 10, 2); // Taxa da plataforma
            $table->unsignedDecimal('creator_revenue', 10, 2); // Receita do criador
            $table->enum('payment_status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('payment_method')->nullable(); // Método de pagamento
            $table->string('transaction_id')->nullable(); // ID da transação externa
            $table->timestamp('purchased_at')->nullable();
            $table->timestamp('access_expires_at')->nullable(); // Null = acesso vitalício
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            
            // Um usuário só pode comprar o mesmo curso uma vez (considerando soft deletes)
            $table->unique(['user_id', 'course_id', 'deleted_at'], 'user_course_purchase_unique');
            
            // Indexes
            $table->index(['payment_status', 'purchased_at'], 'purchases_payment_status_purchased_at_index');
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