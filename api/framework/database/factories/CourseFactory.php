<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['draft', 'pending', 'approved', 'rejected', 'published'];
        $status = $this->faker->randomElement($statuses);
        
        return [
            'uuid' => Str::uuid(),
            'creator_id' => User::factory(),
            'title' => $this->faker->sentence(3, 6),
            'description' => $this->faker->paragraphs(3, true),
            'short_description' => $this->faker->sentence(10, 20),
            'price' => $this->faker->randomFloat(2, 29.90, 299.90),
            'thumbnail' => $this->faker->imageUrl(640, 480, 'business'),
            'preview_video' => $this->faker->url(),
            'preview_content' => $this->faker->paragraph(),
            'status' => $status,
            'rejection_reason' => $status === 'rejected' ? $this->faker->sentence() : null,
            'approved_by' => $status === 'approved' || $status === 'published' ? User::factory() : null,
            'approved_at' => $status === 'approved' || $status === 'published' ? $this->faker->dateTimeBetween('-1 year', 'now') : null,
            'total_lessons' => $this->faker->numberBetween(5, 50),
            'total_duration' => $this->faker->numberBetween(60, 1800), // 1-30 horas em minutos
            'total_sales' => $this->faker->numberBetween(0, 1000),
            'total_revenue' => $this->faker->randomFloat(2, 0, 50000),
            'rating' => $this->faker->randomFloat(2, 0, 5),
            'total_ratings' => $this->faker->numberBetween(0, 500),
            'tags' => json_encode($this->faker->words(3)),
            'requirements' => json_encode($this->faker->sentences(3)),
            'objectives' => json_encode($this->faker->sentences(5)),
            'is_featured' => $this->faker->boolean(20),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the course is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'approved_by' => User::factory(),
            'approved_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Indicate that the course is pending approval.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the course is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }
} 