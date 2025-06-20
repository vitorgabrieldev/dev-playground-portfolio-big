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
            // Criar 2-5 cursos por criador
            $numCourses = rand(2, 5);
            
            for ($i = 0; $i < $numCourses; $i++) {
                $course = Course::factory()->create([
                    'creator_id' => $creator->id,
                    'status' => 'published',
                    'approved_by' => User::first()->id,
                    'approved_at' => now(),
                ]);

                // Associar curso a uma categoria aleatória
                $category = $categories->random();
                $course->categories()->attach($category->id);
            }
        }

        // Criar alguns cursos em destaque
        Course::factory(3)->published()->create()->each(function ($course) use ($categories) {
            $course->update(['is_featured' => true]);
            $category = $categories->random();
            $course->categories()->attach($category->id);
        });
    }
} 