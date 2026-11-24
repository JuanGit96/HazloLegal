<?php

declare(strict_types=1);

namespace App\Filament\Lawyer\Pages;

use App\Domain\Lawyers\Services\CurrentLawyer;
use App\Domain\Subscriptions\Models\LawyerPlan;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

/**
 * Qué plan tiene y qué le da.
 *
 * Se muestra lo que el plan hace de verdad —si aparece en los instructivos,
 * si recibe avisos de casos, si ve contactos—, no una lista de adjetivos.
 * Un abogado que no entiende qué paga no renueva.
 */
class MySubscription extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static string|\UnitEnum|null $navigationGroup = 'Mi presencia';

    protected static ?string $navigationLabel = 'Mi suscripción';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Mi suscripción';

    protected string $view = 'filament.lawyer.pages.my-subscription';

    public function subscription(): ?\App\Domain\Subscriptions\Models\LawyerSubscription
    {
        return app(CurrentLawyer::class)->get()?->subscription;
    }

    /** Lo que incluye el plan, en términos de lo que el sistema hace. */
    public function features(): Collection
    {
        $plan = $this->subscription()?->plan;

        if (! $plan instanceof LawyerPlan) {
            return collect();
        }

        return collect([
            ['includes_profile_page', 'Perfil público en el directorio', 'Tu ficha con especialidad, ciudad y biografía, indexable en Google.'],
            ['includes_case_alerts', 'Avisos de casos nuevos', 'Te escribimos cuando llega un caso de tu área. Te postulas y el cliente elige.'],
            ['includes_pdf_placement', 'Tu nombre en los instructivos', 'El PDF que el cliente imprime y lleva a la ventanilla lleva tus datos.'],
            ['includes_lead_data', 'Acceso a contactos', 'Personas de tus áreas que autorizaron que un abogado las contacte.'],
            ['includes_featured', 'Destacado en el directorio', 'Apareces antes que los demás en tu área.'],
            ['includes_blog_placement', 'Presencia en los artículos', 'Tu perfil se ofrece al final de las guías de tu especialidad.'],
        ])->map(fn (array $row) => [
            'included' => (bool) $plan->{$row[0]},
            'title' => $row[1],
            'description' => $row[2],
        ]);
    }

    /** Lo que podría tener si subiera de plan. */
    public function upgrades(): Collection
    {
        $current = $this->subscription()?->plan;

        return LawyerPlan::query()
            ->where('is_active', true)
            ->when($current, fn ($q) => $q->where('price_monthly', '>', $current->price_monthly))
            ->orderBy('price_monthly')
            ->get();
    }
}
