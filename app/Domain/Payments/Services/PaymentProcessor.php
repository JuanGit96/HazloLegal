<?php

declare(strict_types=1);

namespace App\Domain\Payments\Services;

use App\Domain\Orders\Enums\PaymentMethod;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Services\OrderService;
use App\Domain\Payments\Enums\PaymentStatus;
use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\Subscriptions\Models\LawyerSubscription;
use App\Domain\Subscriptions\Services\SubscriptionPromoService;
use App\Domain\Subscriptions\Services\SubscriptionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Lo que pasa cuando Wompi dice que un cobro cambió de estado.
 *
 * Traduce el evento a lo que significa para el negocio: un pedido que se
 * pone en marcha o una suscripción que se renueva. Es idempotente porque un
 * webhook puede llegar dos veces, y cobrarle o entregarle dos veces a
 * alguien por un reintento de la pasarela sería culpa nuestra.
 */
final class PaymentProcessor
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly SubscriptionService $subscriptions,
        private readonly SubscriptionPromoService $promos,
        private readonly WompiGateway $wompi,
    ) {}

    public function apply(PaymentTransaction $transaction, array $data): PaymentTransaction
    {
        $status = PaymentStatus::fromWompi((string) data_get($data, 'status', ''));

        // Ya estaba aprobada: un reenvío del webhook no vuelve a entregar.
        // Y una ya anulada tampoco se resucita: el cobro de verificación
        // termina anulado a propósito, y un evento tardío de Wompi no puede
        // volver a marcarlo como pagado.
        if ($transaction->isApproved() || $transaction->voided_at !== null) {
            return $transaction;
        }

        if (! $this->matches($transaction, $data)) {
            return $transaction;
        }

        DB::transaction(function () use ($transaction, $data, $status) {
            $transaction->update([
                'status' => $status,
                'gateway_transaction_id' => data_get($data, 'id'),
                'payment_method' => data_get($data, 'payment_method_type'),
                'status_message' => data_get($data, 'status_message'),
                'payload' => $data,
                'paid_at' => $status === PaymentStatus::Approved ? now() : null,
            ]);
        });

        if ($status === PaymentStatus::Approved) {
            $this->fulfil($transaction->refresh());
        }

        if ($status === PaymentStatus::Declined) {
            $this->handleDecline($transaction->refresh());
        }

        return $transaction->refresh();
    }

    /**
     * Comprueba que los datos correspondan a esta transacción.
     *
     * Sin esto, pasar el identificador de cualquier transacción aprobada
     * —los de otro comercio incluidos— marcaría como pagado el pedido que se
     * quisiera. La referencia y el monto tienen que coincidir con lo que
     * nosotros abrimos.
     */
    private function matches(PaymentTransaction $transaction, array $data): bool
    {
        $reference = (string) data_get($data, 'reference', '');
        $amount = (int) data_get($data, 'amount_in_cents', 0);

        if ($reference !== '' && ! hash_equals($transaction->reference, $reference)) {
            Log::warning('Datos de pago con referencia que no corresponde', [
                'esperada' => $transaction->reference,
                'recibida' => $reference,
            ]);

            return false;
        }

        if ($amount > 0 && $amount !== (int) $transaction->amount_in_cents) {
            Log::warning('Datos de pago con un monto distinto al abierto', [
                'reference' => $transaction->reference,
                'esperado' => $transaction->amount_in_cents,
                'recibido' => $amount,
            ]);

            return false;
        }

        return true;
    }

    /** Entrega lo que se pagó. */
    private function fulfil(PaymentTransaction $transaction): void
    {
        $payable = $transaction->payable;

        // El cobro de verificación no es un pago: son $1.500 que se anulan.
        // Tratarlo como pago activaría la suscripción con un dinero que
        // vamos a devolver, y le pondría fecha de renovación a un plan que
        // todavía no ha empezado.
        if ($transaction->isVerification()) {
            if ($payable instanceof LawyerSubscription) {
                $this->completeVerification($payable, $transaction);
            }

            return;
        }

        if ($payable instanceof Order) {
            $this->orders->confirmPayment(
                $payable,
                $this->methodFrom($transaction),
                $transaction->gateway_transaction_id,
            );

            return;
        }

        if ($payable instanceof LawyerSubscription) {
            // El banco aprobó un cobro real contra esa tarjeta: eso es lo
            // que la da por buena, no haberla registrado.
            $this->subscriptions->confirmCard($payable);
            $this->subscriptions->activate($payable, $transaction);
        }
    }

    /**
     * Cierra el alta cuando el banco tardó más que la pantalla.
     *
     * El abogado ya se fue; aquí se anula la verificación y se arranca el
     * periodo de invitación que quedó en el aire.
     */
    private function completeVerification(LawyerSubscription $subscription, PaymentTransaction $transaction): void
    {
        // Se anula primero. Que la anulación falle no invalida lo aprendido
        // —la tarjeta sirve—, pero deja $1.500 cobrados que hay que
        // devolver; `voidTransaction` lo deja escrito en el registro.
        $this->wompi->voidTransaction($transaction);

        $this->subscriptions->confirmCard($subscription);

        $quote = $this->promos->quoteFor($subscription);

        if ($quote !== null && $subscription->trial_ends_at === null) {
            $this->subscriptions->beginPromoPeriod($subscription, $quote);
        }
    }

    /**
     * Un rechazo con la tarjeta recién guardada: se olvida.
     *
     * Dejarla puesta haría que el cobro diario la intentara cada mañana y
     * fuera acumulando rechazos, que es justo lo que lleva a un banco a
     * bloquear a un comercio.
     */
    private function handleDecline(PaymentTransaction $transaction): void
    {
        $payable = $transaction->payable;

        if ($payable instanceof LawyerSubscription && $payable->card_validated_at === null) {
            $this->subscriptions->forgetCard($payable);
        }
    }

    /** Traduce el medio de pago de Wompi al nuestro. */
    private function methodFrom(PaymentTransaction $transaction): PaymentMethod
    {
        return match (strtoupper((string) $transaction->payment_method)) {
            'NEQUI' => PaymentMethod::Nequi,
            'PSE' => PaymentMethod::Pse,
            'BANCOLOMBIA_TRANSFER' => PaymentMethod::Transfer,
            default => PaymentMethod::Card,
        };
    }
}
