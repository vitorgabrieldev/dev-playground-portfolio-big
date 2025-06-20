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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Polimórfico: permite notificar qualquer modelo (User, Customer, etc)
            $table->unsignedBigInteger('notifiable_id');
            $table->string('notifiable_type'); // Ex: App\\Models\\User, App\\Models\\Customer
            $table->index(['notifiable_id', 'notifiable_type'], 'notifications_notifiable_index');

            // Classificação da notificação
            $table->string('type'); // Ex: system, purchase, payout, lesson, etc
            $table->string('channel')->default('web'); // Ex: web, email, push, sms
            $table->enum('priority', ['low', 'normal', 'high'])->default('normal');

            // Conteúdo
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Dados adicionais (payload)
            $table->string('action_url')->nullable(); // URL de ação

            // Controle de leitura
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices para performance
            $table->index(['is_read', 'created_at'], 'notifications_read_created_index');
        });

        // Tabela associativa para controle de leitura e exclusão de notificações por customer
        Schema::create('customer_has_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('notification_id');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('notification_id')->references('id')->on('notifications')->onDelete('cascade');
            $table->unique(['customer_id', 'notification_id'], 'customer_notification_unique');
            $table->index(['customer_id', 'is_read', 'is_deleted']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('customer_has_notifications');
    }
}; 