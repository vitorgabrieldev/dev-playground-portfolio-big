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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('creator_id'); // ID do usuário criador
            $table->string('title');
            $table->text('description');
            $table->text('short_description')->nullable();
            $table->unsignedDecimal('price', 10, 2);
            $table->string('thumbnail')->nullable(); // Imagem de capa
            $table->string('preview_video')->nullable(); // URL do vídeo de prévia
            $table->text('preview_content')->nullable(); // Conteúdo de prévia
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'published'])->default('draft');
            $table->text('rejection_reason')->nullable(); // Motivo da rejeição
            $table->unsignedBigInteger('approved_by')->nullable(); // Admin que aprovou
            $table->timestamp('approved_at')->nullable();
            $table->unsignedInteger('total_lessons')->default(0);
            $table->unsignedInteger('total_duration')->default(0); // Duração total em minutos
            $table->unsignedInteger('total_sales')->default(0);
            $table->unsignedDecimal('total_revenue', 12, 2)->default(0);
            $table->unsignedDecimal('rating', 3, 2)->default(0); // Média das avaliações
            $table->unsignedInteger('total_ratings')->default(0);
            $table->json('tags')->nullable(); // Tags do curso
            $table->json('requirements')->nullable(); // Pré-requisitos
            $table->json('objectives')->nullable(); // Objetivos do curso
            $table->string('level')->default('beginner'); // Nível (beginner, intermediate, advanced)
            $table->string('language')->default('pt-BR'); // Idioma
            $table->unsignedInteger('max_students')->nullable(); // Limite de alunos
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['status', 'is_active', 'is_featured'], 'courses_status_active_featured_index');
            $table->index(['creator_id', 'status'], 'courses_creator_status_index');
            $table->index(['price', 'rating', 'total_sales'], 'courses_price_rating_sales_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
}; 