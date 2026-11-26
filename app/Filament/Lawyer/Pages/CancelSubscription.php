<?php

declare(strict_types=1);

namespace App\Filament\Lawyer\Pages;

use App\Domain\Analytics\Enums\MetricKind;
use App\Domain\Analytics\Services\MetricRecorder;
use App\Domain\Lawyers\Services\CurrentLawyer;
use App\Domain\Subscriptions\Models\LawyerPlan;
use App\Domain\Subscriptions\Models\LawyerSubscription;
use App\Domain\Subscriptions\Services\SubscriptionService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

/**
 * La salida.
 *
 * Es el único sitio del portal donde se ofrece un plan más barato, y es a
 * propósito: un botón de «bajarme de plan» en la pantalla de la cuenta le
 * planta la idea a quien no la traía. Aquí ya decidió irse, así que la
 * opción barata recupera plata en vez de regalarla.
 *
 * Lo que se le enseña antes de confirmar son sus propias cifras, no
 * argumentos: cuánta gente lo vio y cuántos le escribieron este mes. Si
 * esos números son malos, se va con razón y es mejor saberlo; si son
 * buenos, nadie se lo había dicho.
 *
 * Nada de lo que se decide aquí ocurre hoy. El periodo está pagado.
 */
class CancelSubscription extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowRightStartOnRectangle;

    protected static ?string $slug = 'cancelar-suscripcion';

    protected static ?string $title = 'Antes de irte';

    protected string $view = 'filament.lawyer.pages.cancel-subscription';

    /** El plan más barato que está mirando, si ya eligió uno. */
    public ?int $plan = null;

    public bool $cancelando = false;

    public ?string $motivo = null;

    /** @var array<string, string> */
    public const MOTIVOS = [
        'caro' => 'Es caro para lo que recibo',
        'pocos_casos' => 'No me llegan casos de mi área',
        'sin_tiempo' => 'No tengo tiempo de atenderlos',
        'ya_tengo' => 'Ya consigo clientes por otro lado',
        'cierro' => 'Dejo de ejercer o cambio de actividad',
        'otro' => 'Otra razón',
    ];

    /** No se anuncia en el menú: a la salida se llega buscándola. */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function subscription(): ?LawyerSubscription
    {
        return app(CurrentLawyer::class)->get()?->subscription;
    }

    /** Los planes más baratos que el suyo. Nunca los más caros: aquí no se vende. */
    public function cheaperPlans(): Collection
    {
        $actual = $this->subscription()?->plan;

        if (! $actual instanceof LawyerPlan) {
            return collect();
        }

        return LawyerPlan::query()
            ->where('is_active', true)
            ->where('price_monthly', '<', $actual->price_monthly)
            ->orderByDesc('price_monthly')
            ->get();
    }

    /**
     * Lo que ese plan conserva y lo que deja de incluir.
     *
     * Las dos listas, y la de pérdidas primero. Dejar que alguien se baje
     * sin saber qué pierde es ganarse la queja del mes siguiente.
     *
     * @return array{conserva: list<string>, pierde: list<string>}
     */
    public function comparison(LawyerPlan $plan): array
    {
        $actual = $this->subscription()?->plan;

        $rasgos = [
            'includes_profile_page' => 'Perfil público en el directorio',
            'includes_case_alerts' => 'Avisos de casos nuevos',
            'includes_pdf_placement' => 'Tu nombre en los instructivos',
            'includes_lead_data' => 'Acceso a contactos',
            'includes_featured' => 'Destacado en el directorio',
            'includes_blog_placement' => 'Presencia en los artículos',
        ];

        $conserva = [];
        $pierde = [];

        foreach ($rasgos as $campo => $titulo) {
            if (! (bool) ($actual?->{$campo} ?? false)) {
                continue;
            }

            (bool) $plan->{$campo} ? $conserva[] = $titulo : $pierde[] = $titulo;
        }

        return ['conserva' => $conserva, 'pierde' => $pierde];
    }

    /**
     * Sus cifras de los últimos 30 días.
     *
     * @return list<array{label: string, value: int}>
     */
    public function numbers(): array
    {
        $lawyer = app(CurrentLawyer::class)->get();

        if (! $lawyer) {
            return [];
        }

        $kinds = [
            MetricKind::DirectoryImpression,
            MetricKind::ProfileView,
            MetricKind::ContactClick,
            MetricKind::ArticleView,
            MetricKind::CaseInvitation,
        ];

        $totales = app(MetricRecorder::class)->totals($lawyer, $kinds, now()->subDays(30));

        return collect($kinds)
            ->map(fn (MetricKind $kind) => [
                'label' => $kind->getLabel(),
                'value' => $totales[$kind->value],
            ])
            ->all();
    }

    public function chosenPlan(): ?LawyerPlan
    {
        return $this->plan === null
            ? null
            : $this->cheaperPlans()->firstWhere('id', $this->plan);
    }

    // -------------------------------------------------- Pasos

    public function choose(int $plan): void
    {
        $this->plan = $plan;
        $this->cancelando = false;
    }

    public function startCancelling(): void
    {
        $this->cancelando = true;
        $this->plan = null;
    }

    public function back(): void
    {
        $this->plan = null;
        $this->cancelando = false;
        $this->motivo = null;
    }

    // -------------------------------------------------- Decisiones

    public function confirmChange(): void
    {
        $subscription = $this->subscription();
        $plan = $this->chosenPlan();

        // Se comprueba aquí y no solo al pintar: esconder una opción no es
        // impedirla, y por aquí se cambia lo que se cobra.
        if (! $subscription || ! $plan instanceof LawyerPlan) {
            $this->back();

            return;
        }

        app(SubscriptionService::class)->scheduleChange($subscription, $plan);

        Notification::make()
            ->success()
            ->title('Listo, queda programado')
            ->body("Sigues con todo lo de tu plan hasta el {$this->changesOn()}. Ese día pasas a {$plan->name}.")
            ->send();

        $this->back();
        $this->redirect(MySubscription::getUrl());
    }

    public function confirmCancellation(): void
    {
        $subscription = $this->subscription();

        if (! $subscription || ! array_key_exists((string) $this->motivo, self::MOTIVOS)) {
            return;
        }

        app(SubscriptionService::class)->scheduleCancellation($subscription, $this->motivo);

        Notification::make()
            ->warning()
            ->title('Tu plan termina el '.$this->changesOn())
            ->body('No te cobramos de nuevo. Hasta ese día conservas todo, y puedes volver atrás cuando quieras.')
            ->send();

        $this->back();
        $this->redirect(MySubscription::getUrl());
    }

    public function keepEverything(): void
    {
        $subscription = $this->subscription();

        if ($subscription) {
            app(SubscriptionService::class)->resume($subscription);

            Notification::make()
                ->success()
                ->title('Todo sigue igual')
                ->send();
        }

        $this->redirect(MySubscription::getUrl());
    }

    public function changesOn(): string
    {
        return $this->subscription()?->changesOn()?->translatedFormat('j \d\e F \d\e Y') ?? 'la próxima renovación';
    }
}
