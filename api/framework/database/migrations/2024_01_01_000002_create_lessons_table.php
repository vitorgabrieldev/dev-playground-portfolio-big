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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('course_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['video', 'text', 'pdf', 'link', 'quiz'])->default('video');
            $table->text('content')->nullable(); // Conteúdo da aula (texto, URL do vídeo, etc.)
            $table->string('file_path')->nullable(); // Caminho do arquivo (PDF, vídeo)
            $table->string('file_name')->nullable(); // Nome original do arquivo
            $table->unsignedBigInteger('file_size')->nullable(); // Em bytes
            $table->string('file_mime')->nullable(); // Tipo MIME do arquivo
            $table->unsignedInteger('duration')->nullable(); // Duração em segundos
            $table->integer('order')->default(0); // Ordem da aula no curso
            $table->boolean('requires_completion')->default(false); // Se precisa completar para avançar
            $table->boolean('is_preview')->default(false); // Se é aula de prévia gratuita
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['course_id', 'order'], 'lessons_course_order_index');
            $table->index(['course_id', 'is_preview'], 'lessons_course_preview_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
}; 