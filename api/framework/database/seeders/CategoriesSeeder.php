<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Programação',
                'description' => 'Cursos de programação e desenvolvimento de software',
                'icon' => 'code',
                'color' => '#3B82F6',
                'order' => 1,
            ],
            [
                'name' => 'Design',
                'description' => 'Cursos de design gráfico, UI/UX e design digital',
                'icon' => 'design',
                'color' => '#8B5CF6',
                'order' => 2,
            ],
            [
                'name' => 'Marketing Digital',
                'description' => 'Cursos de marketing digital, SEO e redes sociais',
                'icon' => 'business',
                'color' => '#10B981',
                'order' => 3,
            ],
            [
                'name' => 'Negócios',
                'description' => 'Cursos de empreendedorismo e gestão de negócios',
                'icon' => 'business',
                'color' => '#F59E0B',
                'order' => 4,
            ],
            [
                'name' => 'Música',
                'description' => 'Cursos de música, instrumentos e produção musical',
                'icon' => 'music',
                'color' => '#EF4444',
                'order' => 5,
            ],
            [
                'name' => 'Fotografia',
                'description' => 'Cursos de fotografia e edição de imagens',
                'icon' => 'camera',
                'color' => '#06B6D4',
                'order' => 6,
            ],
            [
                'name' => 'Idiomas',
                'description' => 'Cursos de idiomas e comunicação',
                'icon' => 'book',
                'color' => '#84CC16',
                'order' => 7,
            ],
            [
                'name' => 'Saúde e Bem-estar',
                'description' => 'Cursos de saúde, fitness e bem-estar',
                'icon' => 'health',
                'color' => '#EC4899',
                'order' => 8,
            ],
        ];

        foreach ($categories as $category) {
            Category::create([
                'uuid' => Str::uuid(),
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'icon' => $category['icon'],
                'color' => $category['color'],
                'order' => $category['order'],
                'is_active' => true,
            ]);
        }

        $programming = Category::where('name', 'Programação')->first();
        if ($programming) {
            $subCategories = [
                ['name' => 'JavaScript', 'color' => '#F7DF1E'],
                ['name' => 'Python', 'color' => '#3776AB'],
                ['name' => 'React', 'color' => '#61DAFB'],
                ['name' => 'Node.js', 'color' => '#339933'],
            ];

            foreach ($subCategories as $subCategory) {
                Category::create([
                    'uuid' => Str::uuid(),
                    'parent_id' => $programming->id,
                    'name' => $subCategory['name'],
                    'slug' => Str::slug($subCategory['name']),
                    'description' => "Cursos de {$subCategory['name']}",
                    'icon' => 'code',
                    'color' => $subCategory['color'],
                    'order' => rand(1, 100),
                    'is_active' => true,
                ]);
            }
        }
    }
}