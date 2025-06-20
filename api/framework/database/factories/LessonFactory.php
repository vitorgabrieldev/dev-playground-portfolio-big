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
        $types = ['video', 'text', 'pdf', 'link', 'quiz'];
        $type = $this->faker->randomElement($types);
        
        $content = match($type) {
            'video' => $this->faker->url(),
            'text' => $this->faker->paragraphs(3, true),
            'pdf' => 'uploads/lessons/sample.pdf',
            'link' => $this->faker->url(),
            'quiz' => json_encode([
                'questions' => [
                    [
                        'question' => $this->faker->sentence() . '?',
                        'options' => [
                            $this->faker->sentence(),
                            $this->faker->sentence(),
                            $this->faker->sentence(),
                            $this->faker->sentence(),
                        ],
                        'correct_answer' => 0
                    ]
                ]
            ]),
        };
        
        return [
            'uuid' => Str::uuid(),
            'course_id' => Course::factory(),
            'title' => $this->faker->sentence(3, 6),
            'description' => $this->faker->paragraph(),
            'type' => $type,
            'content' => $content,
            'file_path' => $type === 'pdf' ? 'uploads/lessons/sample.pdf' : null,
            'file_name' => $type === 'pdf' ? 'sample.pdf' : null,
            'file_size' => $type === 'pdf' ? '1024000' : null,
            'file_mime' => $type === 'pdf' ? 'application/pdf' : null,
            'duration' => $type === 'video' ? $this->faker->numberBetween(300, 3600) : null, // 5-60 minutos
            'order' => $this->faker->numberBetween(1, 50),
            'is_preview' => $this->faker->boolean(20), // 20% chance de ser prévia
            'is_active' => true,
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