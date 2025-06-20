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
        Schema::create('course_ratings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id'); // Avaliador
            $table->unsignedBigInteger('course_id');
            $table->unsignedTinyInteger('rating'); // 1-5 estrelas
            $table->text('review')->nullable(); // Comentário da avaliação
            $table->boolean('is_verified_purchase')->default(false); // Se é compra verificada
            $table->unsignedInteger('helpful_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            
            // Um usuário só pode avaliar o mesmo curso uma vez (considerando soft deletes)
            $table->unique(['user_id', 'course_id', 'deleted_at'], 'user_course_rating_unique');
            
            // Índices para performance
            $table->index(['course_id', 'rating'], 'ratings_course_rating_index');
            $table->index(['is_verified_purchase', 'helpful_count'], 'ratings_verified_helpful_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_ratings');
    }
}; 