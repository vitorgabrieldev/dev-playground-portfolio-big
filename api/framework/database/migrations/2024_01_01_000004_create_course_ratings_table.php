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
        Schema::create('lesson_ratings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('lesson_id');
            $table->unsignedTinyInteger('rating');
            $table->text('review')->nullable();
            $table->unsignedInteger('helpful_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('cascade');
            
            // Um usuário só pode avaliar a mesma aula uma vez (considerando soft deletes)
            $table->unique(['customer_id', 'lesson_id', 'deleted_at'], 'customer_lesson_rating_unique');
            
            // Índices para performance
            $table->index(['lesson_id', 'rating'], 'ratings_lesson_rating_index');
            $table->index(['helpful_count'], 'ratings_helpful_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_ratings');
    }
}; 