<?php

declare(strict_types=1);

namespace App\Domain\Payments\Services;

use App\Domain\Payments\Enums\PaymentStatus;
use App\Domain\Payments\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Cobros con Wompi, por Checkout Web.
 *
 * El cliente sale a pagar al dominio de Wompi y vuelve. Aquí no entra nunca
 * un número de tarjeta, que es exactamente lo que no queremos custodiar.
 *
 * Dos firmas distintas y conviene no confundirlas:
 *
 * - **Integridad**: se calcula al salir, sobre referencia + monto + moneda.
 *   Impide que alguien edite el precio en el enlace antes de pagar.
 * - **Eventos**: se verifica al volver el webhook, sobre los campos que Wompi
 *   indica en el propio evento. Impide que cualquiera que conozca la URL nos
 *   escriba «aprobado».
 */
final class WompiGateway
{
    public function isEnabled(): bool
    {
        return (bool) config('wompi.enabled')
            && filled(config('wompi.public_key'))
            && filled(config('wompi.integrity_secret'));
    }

    public function isSandbox(): bool
    {
        return config('wompi.mode') !== 'production';
    }

    /**
     * Abre un intento de cobro para lo que sea que se esté pagando.
     *
     * @param  Model  $payable  Pedido o suscripción
     * @param  int  $amount  En pesos enteros
     */
    public function open(Model $payable, int $amount, string $email, string $prefix = 'HL'): PaymentTransaction
    {
        // Wompi rechaza dos transacciones con la misma referencia, así que el
        // sufijo permite reintentar un pago fallido sin inventar otro pedido.
        $reference = $prefix.'-'.$payable->getKey().'-'.strtoupper(Str::random(6));

        return PaymentTransaction::query()->create([
            'payable_type' => $payable->getMorphClass(),
            'payable_id' => $payable->getKey(),
            'gateway' => 'wompi',
            'reference' => $reference,
            'amount_in_cents' => $amount * 100,
            'currency' => config('wompi.currency'),
            'status' => PaymentStatus::Pending,
            'customer_email' => $email,
        ]);
    }

    /** La firma que Wompi exige para no dejar alterar el monto en el enlace. */
    public function integritySignature(PaymentTransaction $transaction): string
    {
        return hash('sha256', implode('', [
            $transaction->reference,
            (string) $transaction->amount_in_cents,
            $transaction->currency,
            (string) config('wompi.integrity_secret'),
        ]));
    }

    /** Los campos del formulario que sale hacia el checkout de Wompi. */
    public function checkoutFields(PaymentTransaction $transaction, string $redirectUrl): array
    {
        return [
            'public-key' => (string) config('wompi.public_key'),
            'currency' => $transaction->currency,
            'amount-in-cents' => (string) $transaction->amount_in_cents,
            'reference' => $transaction->reference,
            'signature:integrity' => $this->integritySignature($transaction),
            'redirect-url' => $redirectUrl,
            'customer-data:email' => (string) $transaction->customer_email,
        ];
    }

    public function checkoutUrl(): string
    {
        return config('wompi.urls.'.($this->isSandbox() ? 'sandbox' : 'production').'.checkout');
    }

    /**
     * Comprueba la firma de un evento del webhook.
     *
     * Wompi dice en el propio evento qué propiedades firmó y en qué orden; se
     * concatenan esos valores, el timestamp y el secreto. Confiar en el
     * cuerpo sin verificar esto sería dejar que cualquiera marque pedidos
     * como pagados.
     */
    public function verifyEvent(array $event): bool
    {
        $checksum = data_get($event, 'signature.checksum');
        $properties = data_get($event, 'signature.properties', []);
        $timestamp = data_get($event, 'timestamp');

        if (! $checksum || ! is_array($properties) || ! $timestamp) {
            return false;
        }

        $concatenated = '';

        foreach ($properties as $property) {
            $concatenated .= (string) data_get($event, 'data.'.$property, '');
        }

        $concatenated .= (string) $timestamp.(string) config('wompi.events_secret');

        return hash_equals(hash('sha256', $concatenated), (string) $checksum);
    }

    /**
     * Consulta el estado real de una transacción en Wompi.
     *
     * Si falla se registra: devolver null en silencio fue lo que hizo que un
     * 401 por apuntar al entorno equivocado pasara desapercibido mientras los
     * pagos aprobados no se confirmaban.
     */
    public function fetch(string $gatewayTransactionId): ?array
    {
        $base = $this->apiUrl();

        try {
            $response = Http::withToken((string) config('wompi.private_key'))
                ->timeout(15)
                ->get("{$base}/transactions/{$gatewayTransactionId}");
        } catch (\Throwable $e) {
            Log::error('No se pudo consultar la transacción en Wompi', [
                'transaction' => $gatewayTransactionId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if (! $response->successful()) {
            Log::error('Wompi rechazó la consulta de la transacción', [
                'transaction' => $gatewayTransactionId,
                'status' => $response->status(),
                'base' => $base,
                'body' => $response->json('error.reason') ?? $response->body(),
            ]);

            return null;
        }

        return $response->json('data');
    }

    public function apiUrl(): string
    {
        return (string) config('wompi.urls.'.($this->isSandbox() ? 'sandbox' : 'production').'.api');
    }
}
