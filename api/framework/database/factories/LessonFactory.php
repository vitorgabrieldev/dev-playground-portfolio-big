<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lesson>
 */
class LessonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'course_id' => Course::factory(),
            'title' => $this->faker->sentence(3, 6),
            'description' => $this->faker->optional()->paragraph(),
            'duration' => $this->faker->optional()->numberBetween(300, 3600), // segundos
            'order' => $this->faker->numberBetween(1, 50),
            'is_preview' => $this->faker->boolean(20),
            'is_active' => true,
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    /**
     * Indicate that the lesson is a preview.
     */
    public function preview(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_preview' => true,
        ]);
    }

    /**
     * Indicate that the lesson is a video.
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'video',
            'content' => $this->faker->url(),
            'duration' => $this->faker->numberBetween(300, 3600),
        ]);
    }

    /**
     * Indicate that the lesson is text.
     */
    public function text(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'text',
            'content' => $this->faker->paragraphs(3, true),
        ]);
    }
} 