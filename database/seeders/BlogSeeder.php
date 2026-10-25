<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Content\Enums\PostStatus;
use App\Domain\Content\Models\Post;
use App\Domain\Content\Models\PostCategory;
use App\Domain\Lawyers\Models\Lawyer;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Salud y EPS', 'slug' => 'salud-y-eps', 'emoji' => '⚕️', 'description' => 'Tutelas, autorizaciones negadas, medicamentos e incapacidades.', 'sort_order' => 1],
            ['name' => 'Deudas y Datacrédito', 'slug' => 'deudas-y-datacredito', 'emoji' => '💳', 'description' => 'Reportes negativos, habeas data, cobros indebidos y centrales de riesgo.', 'sort_order' => 2],
            ['name' => 'Trabajo y liquidaciones', 'slug' => 'trabajo-y-liquidaciones', 'emoji' => '💼', 'description' => 'Liquidaciones, despidos, horas extras y derechos del trabajador.', 'sort_order' => 3],
            ['name' => 'Arriendos y vivienda', 'slug' => 'arriendos-y-vivienda', 'emoji' => '🏠', 'description' => 'Contratos de arrendamiento, incrementos, restituciones y propiedad horizontal.', 'sort_order' => 4],
            ['name' => 'Empresa y contratos', 'slug' => 'empresa-y-contratos', 'emoji' => '🏢', 'description' => 'Constituir empresa, contratos comerciales, NDA y propiedad intelectual.', 'sort_order' => 5],
            ['name' => 'Tránsito y multas', 'slug' => 'transito-y-multas', 'emoji' => '🚗', 'description' => 'Fotomultas, comparendos, traspasos y procedimientos de tránsito.', 'sort_order' => 6],
        ];

        foreach ($categories as $category) {
            PostCategory::query()->updateOrCreate(['slug' => $category['slug']], [
                ...$category,
                'seo_title' => "{$category['name']}: guías legales gratis | Hazlo Legal",
                'seo_description' => $category['description'].' Explicado por abogados colombianos.',
            ]);
        }

        $categoryIds = PostCategory::query()->pluck('id', 'slug');
        $lawyerIds = Lawyer::query()->pluck('id', 'email');
        $documentIds = LegalDocument::query()->pluck('id', 'slug');

        $posts = require database_path('seeders/data/posts.php');

        foreach ($posts as $index => $post) {
            $categorySlug = $post['category'];
            $authorEmail = $post['author'];
            $documentSlug = $post['document'];
            unset($post['category'], $post['author'], $post['document']);

            Post::query()->updateOrCreate(['slug' => $post['slug']], [
                ...$post,
                'post_category_id' => $categoryIds[$categorySlug] ?? null,
                'author_lawyer_id' => $lawyerIds[$authorEmail] ?? null,
                'related_document_id' => $documentIds[$documentSlug] ?? null,
                'status' => PostStatus::Published,
                // Fechas escalonadas: un blog con todo publicado el mismo día
                // se ve artificial, y Google mira la frecuencia de publicación.
                'published_at' => now()->subDays(($index + 1) * 9),
                'reviewed_at' => now()->subDays($index + 1),
            ]);
        }
    }
}
