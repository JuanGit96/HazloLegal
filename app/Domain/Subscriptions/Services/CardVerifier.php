<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions\Services;

use App\Domain\Payments\Enums\PaymentPurpose;
use App\Domain\Payments\Enums\PaymentStatus;
use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\Payments\Services\PaymentProcessor;
use App\Domain\Payments\Services\WompiGateway;
use App\Domain\Subscriptions\Data\CardVerification;
use App\Domain\Subscriptions\Enums\CardCheck;
use App\Domain\Subscriptions\Models\LawyerSubscription;
use Illuminate\Support\Facades\Log;

/**
 * Comprueba que una tarjeta sirve de verdad.
 *
 * Registrarla en la pasarela no prueba nada: Wompi devuelve «disponible»
 * para una tarjeta que después el banco rechaza. Lo único que distingue una
 * de otra es intentar cobrarla, así que se cobra el mínimo que Wompi admite
 * —$1.500— y se anula en el mismo segundo.
 *
 * Esto atrapa lo que importa: tarjetas sin cupo, bloqueadas, y los débitos
 * que no están habilitados para compras en línea, que en Colombia son
 * muchos. Lo que el abogado ve es una retención que se cae sola; se le dice
 * antes de hacerla, porque un cargo que nadie anunció es una disputa con el
 * banco y eso cuesta más que el mes que se iba a cobrar.
 */
final class CardVerifier
{
    public function __construct(
        private readonly WompiGateway $wompi,
        private readonly PaymentProcessor $processor,
    ) {}

    /**
     * Cobra el mínimo contra la tarjeta guardada y lo anula.
     *
     * Para el camino en que hoy no se cobra nada —los meses de invitación—,
     * que es justo donde sin esto no sabríamos si la tarjeta sirve hasta
     * tres meses después.
     */
    public function verify(LawyerSubscription $subscription): CardVerification
    {
        if (! filled($subscription->payment_source_id)) {
            return new CardVerification(CardCheck::Failed);
        }

        $transaction = $this->wompi->open(
            payable: $subscription,
            amount: $this->amount(),
            email: (string) $subscription->lawyer?->email,
            prefix: 'VER',
            purpose: PaymentPurpose::Verification,
        );

        if ($this->wompi->chargeStoredCard($transaction, (string) $subscription->payment_source_id) === null) {
            $transaction->update(['status' => PaymentStatus::Error]);

            return new CardVerification(CardCheck::Failed, $transaction);
        }

        $desenlace = $this->awaitOutcome($transaction);
        $transaction->refresh();

        if ($desenlace !== CardCheck::Approved) {
            return new CardVerification($desenlace, $transaction, $transaction->status_message);
        }

        // Anular y arrancar el plan lo hizo ya el procesador, que es quien
        // cumple pase lo que pase —aquí o más tarde desde el webhook—. Si
        // la transacción sigue sin anular, quedó dinero que devolver.
        if ($transaction->voided_at === null) {
            Log::error('Quedó un cobro de verificación sin anular', [
                'reference' => $transaction->reference,
                'monto' => $transaction->amount(),
                'suscripcion' => $transaction->payable_id,
            ]);
        }

        return new CardVerification(
            outcome: CardCheck::Approved,
            transaction: $transaction,
            pendingRefund: $transaction->voided_at === null,
        );
    }

    /**
     * Espera el desenlace de un cobro ya lanzado.
     *
     * Wompi resuelve tarjeta en décimas de segundo, pero la transacción nace
     * en pendiente y el resultado real lo confirma el webhook. Se consulta
     * un par de veces para poder contestarle al abogado en la misma
     * pantalla; si para entonces no hay respuesta, se suelta y lo cierra el
     * webhook. Tener a alguien mirando un spinner no mejora el desenlace.
     */
    public function awaitOutcome(PaymentTransaction $transaction): CardCheck
    {
        $intentos = max(1, (int) config('wompi.settle_attempts', 3));
        $espera = max(0, (int) config('wompi.settle_wait_ms', 600)) * 1000;

        for ($intento = 0; $intento < $intentos; $intento++) {
            if ($intento > 0 && $espera > 0) {
                usleep($espera);
            }

            $data = $this->wompi->fetch((string) $transaction->gateway_transaction_id);

            if ($data === null) {
                continue;
            }

            $status = PaymentStatus::fromWompi((string) ($data['status'] ?? ''));

            if ($status === PaymentStatus::Pending) {
                continue;
            }

            // Se pasa por el procesador y no se escribe el estado aquí: es
            // el mismo camino que recorre el webhook, con su comprobación de
            // monto y referencia y su protección contra repeticiones. Dos
            // caminos distintos para lo mismo acabarían divergiendo.
            $this->processor->apply($transaction, $data);

            return $status === PaymentStatus::Approved ? CardCheck::Approved : CardCheck::Declined;
        }

        return CardCheck::Pending;
    }

    /** $1.500: el mínimo que Wompi admite en una transacción. */
    public function amount(): int
    {
        return max(1_500, (int) config('wompi.verification_amount', 1_500));
    }

    public function formattedAmount(): string
    {
        return '$'.number_format($this->amount(), 0, ',', '.');
    }
}
