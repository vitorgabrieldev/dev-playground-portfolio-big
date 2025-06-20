<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producer_balances', function (Blueprint $table) {
            $table->unsignedBigInteger('producer_id')->primary();
            $table->unsignedDecimal('balance_available', 12, 2)->default(0.00);
            $table->unsignedDecimal('balance_blocked', 12, 2)->default(0.00);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('created_at')->useCurrent();
            $table->softDeletes();

            $table->foreign('producer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producer_balances');
    }
}; 