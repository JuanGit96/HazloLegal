<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Payments\Services\WompiGateway;
use App\Domain\Subscriptions\Enums\SubscriptionStatus;
use App\Domain\Subscriptions\Models\LawyerSubscription;
use Illuminate\Console\Command;

/**
 * Cobra las suscripciones que vencen hoy contra la tarjeta registrada.
 *
 * Esto es lo que le da sentido a pedir la tarjeta al principio: sin un cobro
 * automático, los meses gratis serían meses gratis y nada más.
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

    public function handle(WompiGateway $wompi): int
    {
        if (! $wompi->isEnabled()) {
            $this->error('La pasarela está desactivada: revisa la configuración de Wompi.');

            return self::FAILURE;
        }

        $vencidas = LawyerSubscription::query()
            ->with(['lawyer', 'plan', 'promo'])
            ->whereIn('status', [SubscriptionStatus::Trialing, SubscriptionStatus::Active, SubscriptionStatus::PastDue])
            ->whereNotNull('payment_source_id')
            ->whereNotNull('recurring_authorized_at')
            ->whereNotNull('renews_at')
            ->where('renews_at', '<=', now())
            ->get();

        if ($vencidas->isEmpty()) {
            $this->info('No hay renovaciones vencidas.');

            return self::SUCCESS;
        }

        $seco = (bool) $this->option('dry-run');
        $cobradas = 0;

        foreach ($vencidas as $subscription) {
            $monto = $subscription->nextChargeAmount();
            $correo = (string) $subscription->lawyer?->email;

            $this->line(sprintf(
                '%-34s %-14s %s',
                mb_strimwidth((string) $subscription->lawyer?->name, 0, 34, '…'),
                '$'.number_format($monto, 0, ',', '.'),
                $subscription->cardLabel() ?? '—',
            ));

            if ($seco || $monto <= 0 || $correo === '') {
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
            ? $vencidas->count().' renovación(es) vencida(s). No se cobró nada.'
            : $cobradas.' cobro(s) iniciado(s). El resultado lo confirma el webhook.');

        return self::SUCCESS;
    }
}
