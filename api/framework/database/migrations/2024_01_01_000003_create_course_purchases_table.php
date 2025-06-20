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
            
            // Um usuário só pode comprar o mesmo curso uma vez (considerando soft deletes)
            $table->unique(['customer_id', 'course_id', 'deleted_at'], 'customer_course_purchase_unique');
            
            // Indexes
            $table->index(['payment_status', 'release_status', 'purchased_at'], 'purchases_status_release_purchased_index');
            $table->index(['course_id', 'payment_status'], 'purchases_course_payment_status_index');
        });

        // Tabela de saques dos produtores
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

        // Tabela de logs de movimentação de saldo
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

        // Tabela de saldo dos produtores
        Schema::create('producer_balances', function (Blueprint $table) {
            $table->unsignedBigInteger('producer_id')->primary();
            $table->unsignedDecimal('balance_available', 12, 2)->default(0.00);
            $table->unsignedDecimal('balance_blocked', 12, 2)->default(0.00);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('created_at')->useCurrent();
            $table->softDeletes();

            $table->foreign('producer_id')->references('id')->on('customers')->onDelete('cascade');
        });

        // Tabela de métodos de pagamento do usuário
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

        // Tabela de métodos de saque do produtor
        Schema::create('customer_payout_methods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('label')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_code')->nullable();
            $table->string('agency')->nullable();
            $table->string('account')->nullable();
            $table->string('account_type')->nullable();
            $table->string('holder_name')->nullable();
            $table->string('holder_document')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->index(['customer_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producer_balances');
        Schema::dropIfExists('course_payout_logs');
        Schema::dropIfExists('course_payouts');
        Schema::dropIfExists('course_purchases');
        Schema::dropIfExists('customer_payment_methods');
        Schema::dropIfExists('customer_payout_methods');
    }
}; 