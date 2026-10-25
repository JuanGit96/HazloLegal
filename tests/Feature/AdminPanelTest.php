<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\BlogSeeder;
use Database\Seeders\DemoActivitySeeder;
use Database\Seeders\FaqSeeder;
use Database\Seeders\LawyerSeeder;
use Database\Seeders\LegalDocumentSeeder;
use Database\Seeders\ServicePlanSeeder;
use Database\Seeders\TestimonialSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            ServicePlanSeeder::class,
            LawyerSeeder::class,
            LegalDocumentSeeder::class,
            FaqSeeder::class,
            TestimonialSeeder::class,
            BlogSeeder::class,
            DemoActivitySeeder::class,
        ]);

        $this->actingAs(User::factory()->create());
    }

    public static function resourcePages(): array
    {
        return [
            'pedidos' => ['filament.admin.resources.orders.index'],
            'casos complejos' => ['filament.admin.resources.legal-cases.index'],
            'garantías' => ['filament.admin.resources.guarantee-claims.index'],
            'trámites' => ['filament.admin.resources.legal-documents.index'],
            'planes' => ['filament.admin.resources.service-plans.index'],
            'abogados' => ['filament.admin.resources.lawyers.index'],
            'artículos' => ['filament.admin.resources.posts.index'],
            'categorías' => ['filament.admin.resources.post-categories.index'],
            'testimonios' => ['filament.admin.resources.testimonials.index'],
            'preguntas frecuentes' => ['filament.admin.resources.faq-entries.index'],
            'leads' => ['filament.admin.resources.leads.index'],
            'usuarios' => ['filament.admin.resources.users.index'],
        ];
    }

    #[DataProvider('resourcePages')]
    public function test_los_listados_del_panel_cargan(string $routeName): void
    {
        $this->get(route($routeName))->assertOk();
    }

    public function test_el_tablero_carga_con_sus_graficas(): void
    {
        $this->get(route('filament.admin.pages.dashboard'))->assertOk();
    }

    public function test_las_paginas_de_hoja_de_ruta_cargan_y_explican_el_modulo(): void
    {
        $pages = [
            'filament.admin.pages.document-automation',
            'filament.admin.pages.pdf-delivery',
            'filament.admin.pages.electronic-signature',
            'filament.admin.pages.payment-gateway',
            'filament.admin.pages.whats-app-module',
            'filament.admin.pages.analytics-module',
            'filament.admin.pages.lawyer-portal',
            'filament.admin.pages.client-portal',
            'filament.admin.pages.invoicing-module',
            'filament.admin.pages.team-and-permissions',
            'filament.admin.pages.seo-module',
        ];

        foreach ($pages as $page) {
            $this->get(route($page))
                ->assertOk()
                ->assertSee('Pendiente de construir')
                ->assertSee('Qué debería hacer');
        }
    }

    public function test_el_panel_exige_autenticacion(): void
    {
        auth()->logout();

        $this->get(route('filament.admin.resources.orders.index'))
            ->assertRedirect(route('filament.admin.auth.login'));
    }

    public function test_las_fichas_de_edicion_cargan(): void
    {
        $order = \App\Domain\Orders\Models\Order::query()->firstOrFail();
        $case = \App\Domain\LegalCases\Models\LegalCase::query()->firstOrFail();
        $lawyer = \App\Domain\Lawyers\Models\Lawyer::query()->firstOrFail();
        $document = \App\Domain\Catalog\Models\LegalDocument::query()->firstOrFail();
        $post = \App\Domain\Content\Models\Post::query()->firstOrFail();

        $this->get(route('filament.admin.resources.orders.edit', $order))->assertOk();
        $this->get(route('filament.admin.resources.legal-cases.edit', $case))->assertOk();
        $this->get(route('filament.admin.resources.lawyers.edit', $lawyer))->assertOk();
        $this->get(route('filament.admin.resources.legal-documents.edit', $document))->assertOk();
        $this->get(route('filament.admin.resources.posts.edit', $post))->assertOk();
    }
}
