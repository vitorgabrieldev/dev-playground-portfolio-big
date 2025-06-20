<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class LessonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::where('status', 'published')->get();

        foreach ($courses as $course) {
            // Criar 5-15 aulas por curso
            $numLessons = rand(5, 15);
            
            for ($i = 1; $i <= $numLessons; $i++) {
                $isPreview = $i <= 2; // Primeiras 2 aulas são prévia
                
                Lesson::factory()->create([
                    'course_id' => $course->id,
                    'order' => $i,
                    'is_preview' => $isPreview,
                ]);
            }
        }
    }
} 