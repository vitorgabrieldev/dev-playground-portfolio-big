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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('fullname');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->text('bio')->nullable();
            $table->string('document')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->string('website')->nullable();
            $table->json('social_links')->nullable();

            // Endereço
            $table->string('address_street')->nullable();
            $table->string('address_number', 20)->nullable();
            $table->string('address_complement', 50)->nullable();
            $table->string('address_neighborhood', 50)->nullable();
            $table->string('address_city', 50)->nullable();
            $table->string('address_state', 30)->nullable();
            $table->string('address_zipcode', 20)->nullable();
            $table->string('address_country', 50)->nullable();

            // Status e controle
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_blocked')->default(false);
            $table->string('blocked_reason')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_ip', 45)->nullable();
            $table->string('register_ip', 45)->nullable();
            $table->string('register_source', 30)->nullable();

            // Preferências
            $table->string('language', 10)->default('pt-BR');
            $table->json('notification_preferences')->nullable();

            // Outros
            $table->string('referral_code', 20)->nullable();
            $table->string('referred_by', 20)->nullable();
            $table->json('custom_data')->nullable();

            $table->string('cpf_front_image')->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
}; 