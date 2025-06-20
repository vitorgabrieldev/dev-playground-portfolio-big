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
            $table->unsignedBigInteger('creator_id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('title');
            $table->text('description');
            $table->text('short_description')->nullable();
            $table->unsignedDecimal('price', 10, 2);
            $table->text('preview_content')->nullable();
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'published'])->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedInteger('total_duration')->default(0);
            $table->unsignedInteger('total_sales')->default(0);
            $table->unsignedDecimal('total_revenue', 12, 2)->default(0);
            $table->unsignedDecimal('rating', 3, 2)->default(0);
            $table->unsignedInteger('total_ratings')->default(0);
            $table->json('tags')->nullable();
            $table->json('requirements')->nullable();
            $table->json('objectives')->nullable();
            $table->string('level')->default('beginner');
            $table->json('languages')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('creator_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('customers')->onDelete('set null');

            $table->index(['status', 'is_active'], 'courses_status_active_featured_index');
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