<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Payments\Services\WompiGateway;
use App\Domain\Subscriptions\Enums\SubscriptionStatus;
use App\Domain\Subscriptions\Models\LawyerSubscription;
use App\Domain\Subscriptions\Services\SubscriptionService;
use Illuminate\Console\Command;

/**
 * Cobra las suscripciones que vencen hoy contra la tarjeta registrada.
 *
 * Esto es lo que le da sentido a pedir la tarjeta al principio: sin un cobro
 * automático, los meses gratis serían meses gratis y nada más.
 *
 * Antes de cobrar aplica lo que el abogado pidió semanas atrás: a quien
 * se va no se le cobra, y a quien se bajó de plan se le cobra el plan
 * nuevo. Por eso la lista de vencidas se arma sin filtrar por tarjeta:
 * una cancelación programada tiene que ejecutarse aunque no haya nada
 * que cobrar.
 *
 * Solo cobra lo que tiene autorización expresa (`recurring_authorized_at`).
 * Quien pagó por Nequi o PSE no deja tarjeta: a ese se le escribe, no se le
 * cobra. Y el resultado llega asíncrono, así que aquí nada se marca como
 * pagado: eso lo confirma el webhook de Wompi.
 */
class SubscriptionsCharge extends Command
{
    protected $signature = 'hl:suscripciones-cobrar {--dry-run : Muestra a quién se cobraría, sin cobrar}';

    protected $description = 'Cobra las renovaciones vencidas contra la tarjeta registrada de cada abogado';

    public function handle(WompiGateway $wompi, SubscriptionService $subscriptions): int
    {
        if (! $wompi->isEnabled()) {
            $this->error('La pasarela está desactivada: revisa la configuración de Wompi.');

            return self::FAILURE;
        }

        $vencidas = LawyerSubscription::query()
            ->with(['lawyer', 'plan', 'scheduledPlan', 'promo'])
            ->whereIn('status', [SubscriptionStatus::Trialing, SubscriptionStatus::Active, SubscriptionStatus::PastDue])
            ->whereNotNull('renews_at')
            ->where('renews_at', '<=', now())
            ->get();

        if ($vencidas->isEmpty()) {
            $this->info('No hay renovaciones vencidas.');

            return self::SUCCESS;
        }

        $seco = (bool) $this->option('dry-run');
        $cobradas = 0;
        $cerradas = 0;

        foreach ($vencidas as $subscription) {
            $nombre = mb_strimwidth((string) $subscription->lawyer?->name, 0, 34, '…');

            if ($seco) {
                $this->line(sprintf('%-34s %s', $nombre, $this->loQueVaAPasar($subscription)));

                continue;
            }

            // Lo pedido se ejecuta antes de mirar la tarjeta: cobrarle el
            // plan viejo a quien se bajó, o cobrarle a quien se va, es la
            // clase de error que termina en una disputa con el banco.
            if ($subscriptions->applyScheduled($subscription)) {
                $this->line(sprintf('%-34s %s', $nombre, 'se va hoy, como pidió. No se cobra.'));
                $cerradas++;

                continue;
            }

            $monto = $subscription->nextChargeAmount();
            $correo = (string) $subscription->lawyer?->email;

            $this->line(sprintf(
                '%-34s %-14s %s',
                $nombre,
                '$'.number_format($monto, 0, ',', '.'),
                $subscription->cardLabel() ?? '—',
            ));

            if ($monto <= 0 || $correo === '') {
                continue;
            }

            if (blank($subscription->payment_source_id) || $subscription->recurring_authorized_at === null) {
                $this->line('  Sin tarjeta autorizada: a este se le escribe, no se le cobra.');

                continue;
            }

            if ($subscription->hasChargeInFlight()) {
                $this->line('  Ya hay un cobro lanzado esperando confirmación. Se deja para mañana.');

                continue;
            }

            $transaction = $wompi->open($subscription, $monto, $correo, 'SUB');

            if ($wompi->chargeStoredCard($transaction, (string) $subscription->payment_source_id) === null) {
                // Queda en mora, no cancelada: un cobro fallido puede ser un
                // cupo lleno, y cerrarle el perfil al abogado por eso sería
                // castigarlo por algo que arregla en un día.
                $subscription->update(['status' => SubscriptionStatus::PastDue]);
                $this->warn('  No se pudo iniciar el cobro. Queda en mora.');

                continue;
            }

            $cobradas++;
        }

        $this->newLine();
        $this->info($seco
            ? $vencidas->count().' renovación(es) vencida(s). No se cobró ni se cambió nada.'
            : $cobradas.' cobro(s) iniciado(s) y '.$cerradas.' cierre(s). El resultado lo confirma el webhook.');

        return self::SUCCESS;
    }

    /** Qué haría hoy con esta suscripción, para el `--dry-run`. */
    private function loQueVaAPasar(LawyerSubscription $subscription): string
    {
        if ($subscription->ends_at !== null && ! $subscription->ends_at->isFuture()) {
            return 'se cierra: pidió irse.';
        }

        $monto = '$'.number_format($subscription->nextChargeAmount(), 0, ',', '.');

        if ($subscription->scheduledPlan !== null) {
            return 'baja a '.$subscription->scheduledPlan->name.' y se le cobra ese plan.';
        }

        if (blank($subscription->payment_source_id) || $subscription->recurring_authorized_at === null) {
            return $monto.' — sin tarjeta autorizada, no se cobra.';
        }

        return $monto.' a '.($subscription->cardLabel() ?? 'su tarjeta');
    }
}
