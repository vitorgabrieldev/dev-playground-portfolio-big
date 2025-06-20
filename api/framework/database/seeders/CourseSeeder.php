<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar alguns usuários criadores
        $creators = User::factory(5)->create();

        // Obter categorias
        $categories = Category::whereNull('parent_id')->get();

        foreach ($creators as $creator) {
            $numCourses = rand(2, 5);
            for ($i = 0; $i < $numCourses; $i++) {
                $course = Course::factory()->create([
                    'creator_id' => $creator->id,
                    'status' => 'published',
                    'approved_by' => User::first()->id,
                    'approved_at' => now(),
                ]);
                $category = $categories->random();
                $course->category()->associate($category);
                $course->save();
            }
        }
        
        Course::factory(3)->published()->create()->each(function ($course) use ($categories) {
            $category = $categories->random();
            $course->category()->associate($category);
            $course->save();
        });
    }
} 