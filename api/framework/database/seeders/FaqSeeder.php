<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'Como funciona a plataforma de cursos?',
                'answer' => 'Nossa plataforma permite que qualquer pessoa crie e venda cursos online. Os cursos passam por uma aprovação antes de serem publicados, garantindo qualidade do conteúdo.',
                'category' => 'Geral',
                'order' => 1,
            ],
            [
                'question' => 'Como posso criar um curso?',
                'answer' => 'Para criar um curso, você precisa se cadastrar na plataforma, acessar a área de criação de cursos e seguir o passo a passo para adicionar conteúdo, aulas e informações do curso.',
                'category' => 'Criadores',
                'order' => 2,
            ],
            [
                'question' => 'Quanto tempo leva para um curso ser aprovado?',
                'answer' => 'O processo de aprovação geralmente leva de 2 a 5 dias úteis. Nossa equipe revisa o conteúdo para garantir que atenda aos padrões de qualidade da plataforma.',
                'category' => 'Criadores',
                'order' => 3,
            ],
            [
                'question' => 'Como funciona o pagamento aos criadores?',
                'answer' => 'Os criadores recebem 85% do valor de cada venda, sendo 15% retidos como taxa da plataforma. Os pagamentos são processados mensalmente.',
                'category' => 'Criadores',
                'order' => 4,
            ],
            [
                'question' => 'Posso assistir aos cursos no celular?',
                'answer' => 'Sim! Nossa plataforma é totalmente responsiva e funciona em dispositivos móveis, tablets e computadores.',
                'category' => 'Alunos',
                'order' => 5,
            ],
            [
                'question' => 'O acesso aos cursos é vitalício?',
                'answer' => 'Sim, quando você compra um curso, você tem acesso vitalício ao conteúdo, incluindo atualizações futuras.',
                'category' => 'Alunos',
                'order' => 6,
            ],
            [
                'question' => 'Posso solicitar reembolso?',
                'answer' => 'Oferecemos reembolso em até 7 dias após a compra, desde que você não tenha assistido mais de 30% do conteúdo do curso.',
                'category' => 'Alunos',
                'order' => 7,
            ],
            [
                'question' => 'Quais formatos de arquivo são aceitos?',
                'answer' => 'Aceitamos vídeos (MP4, AVI), documentos (PDF), imagens (JPG, PNG) e links externos para complementar o conteúdo dos cursos.',
                'category' => 'Criadores',
                'order' => 8,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::create([
                'uuid' => Str::uuid(),
                'question' => $faq['question'],
                'answer' => $faq['answer'],
                'category' => $faq['category'],
                'order' => $faq['order'],
                'is_active' => true,
            ]);
        }
    }
} 