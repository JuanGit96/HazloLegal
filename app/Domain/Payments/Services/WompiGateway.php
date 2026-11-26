<?php

declare(strict_types=1);

namespace App\Domain\Payments\Services;

use App\Domain\Payments\Enums\PaymentPurpose;
use App\Domain\Payments\Enums\PaymentStatus;
use App\Domain\Payments\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
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
    public function open(
        Model $payable,
        int $amount,
        string $email,
        string $prefix = 'HL',
        PaymentPurpose $purpose = PaymentPurpose::Payment,
    ): PaymentTransaction {
        // Wompi rechaza dos transacciones con la misma referencia, así que el
        // sufijo permite reintentar un pago fallido sin inventar otro pedido.
        $reference = $prefix.'-'.$payable->getKey().'-'.strtoupper(Str::random(6));

        return PaymentTransaction::query()->create([
            'payable_type' => $payable->getMorphClass(),
            'payable_id' => $payable->getKey(),
            'gateway' => 'wompi',
            'purpose' => $purpose,
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

    /**
     * Los tokens de aceptación que Wompi exige para guardar una tarjeta.
     *
     * Son dos y distintos: el reglamento del servicio y la autorización de
     * tratamiento de datos. Los dos vienen firmados y con vencimiento, así
     * que se piden poco antes de usarlos y se guardan solo unos minutos.
     *
     * @return array{acceptance: string, permalink: string, personal_data: ?string, personal_data_permalink: ?string}|null
     */
    public function acceptanceTokens(): ?array
    {
        $key = (string) config('wompi.public_key');

        if ($key === '') {
            return null;
        }

        return Cache::remember("wompi.acceptance.{$key}", now()->addMinutes(5), function () use ($key): ?array {
            try {
                $response = Http::timeout(15)->get($this->apiUrl()."/merchants/{$key}");
            } catch (\Throwable $e) {
                Log::error('No se pudieron pedir los tokens de aceptación a Wompi', ['error' => $e->getMessage()]);

                return null;
            }

            if (! $response->successful()) {
                Log::error('Wompi rechazó la consulta del comercio', [
                    'status' => $response->status(),
                    'body' => $response->json('error.reason') ?? $response->body(),
                ]);

                return null;
            }

            $acceptance = $response->json('data.presigned_acceptance');

            if (! is_array($acceptance) || ! filled($acceptance['acceptance_token'] ?? null)) {
                return null;
            }

            return [
                'acceptance' => (string) $acceptance['acceptance_token'],
                'permalink' => (string) ($acceptance['permalink'] ?? ''),
                'personal_data' => $response->json('data.presigned_personal_data_auth.acceptance_token'),
                'personal_data_permalink' => $response->json('data.presigned_personal_data_auth.permalink'),
            ];
        });
    }

    /**
     * Registra una tarjeta como fuente de pago, que es lo que la valida.
     *
     * El número de la tarjeta nunca pasa por aquí: el navegador lo cambia por
     * un token contra Wompi con la llave pública, y lo que viaja a este
     * servidor es ese token.
     *
     * Lo que esto comprueba es que la tarjeta existe, que no está vencida y
     * que la red la admite. **No** garantiza que el banco vaya a aprobar un
     * cobro futuro: eso solo lo demuestra un cobro, y Wompi no permite
     * autorizar por cero pesos. Se dice tal cual en la página, para no
     * prometer una certeza que no tenemos.
     *
     * @return array{id: string, brand: ?string, last_four: ?string}|null
     */
    public function registerCard(string $cardToken, string $email, string $acceptanceToken, ?string $personalDataToken = null): ?array
    {
        $payload = array_filter([
            'type' => 'CARD',
            'token' => $cardToken,
            'customer_email' => $email,
            'acceptance_token' => $acceptanceToken,
            'accept_personal_auth' => $personalDataToken,
        ], fn ($value) => $value !== null);

        try {
            $response = Http::withToken((string) config('wompi.private_key'))
                ->timeout(20)
                ->post($this->apiUrl().'/payment_sources', $payload);
        } catch (\Throwable $e) {
            Log::error('No se pudo registrar la tarjeta en Wompi', ['error' => $e->getMessage()]);

            return null;
        }

        if (! $response->successful() || $response->json('data.status') !== 'AVAILABLE') {
            Log::warning('Wompi no aceptó la tarjeta', [
                'status' => $response->status(),
                'reason' => $response->json('error.reason') ?? $response->json('error.messages') ?? $response->body(),
            ]);

            return null;
        }

        return [
            'id' => (string) $response->json('data.id'),
            'brand' => $response->json('data.public_data.brand') ?? $response->json('data.public_data.type'),
            'last_four' => $response->json('data.public_data.last_four'),
        ];
    }

    /**
     * Cobra contra una tarjeta ya registrada.
     *
     * Devuelve el identificador de la transacción en Wompi. El resultado llega
     * asíncrono —la transacción nace en PENDING— así que quien llame a esto
     * no debe dar nada por aprobado: lo confirma el webhook.
     */
    public function chargeStoredCard(PaymentTransaction $transaction, string $paymentSourceId, int $installments = 1): ?string
    {
        $tokens = $this->acceptanceTokens();

        if ($tokens === null) {
            return null;
        }

        try {
            $response = Http::withToken((string) config('wompi.private_key'))
                ->timeout(25)
                ->post($this->apiUrl().'/transactions', [
                    'amount_in_cents' => (int) $transaction->amount_in_cents,
                    'currency' => $transaction->currency,
                    'customer_email' => (string) $transaction->customer_email,
                    'reference' => $transaction->reference,
                    'payment_source_id' => (int) $paymentSourceId,
                    'acceptance_token' => $tokens['acceptance'],
                    'signature' => $this->integritySignature($transaction),
                    'payment_method' => ['installments' => max(1, $installments)],
                ]);
        } catch (\Throwable $e) {
            Log::error('No se pudo cobrar la tarjeta guardada', [
                'reference' => $transaction->reference,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if (! $response->successful()) {
            Log::error('Wompi rechazó el cobro recurrente', [
                'reference' => $transaction->reference,
                'status' => $response->status(),
                'body' => $response->json('error.reason') ?? $response->body(),
            ]);

            return null;
        }

        $id = $response->json('data.id');

        if ($id !== null) {
            $transaction->update(['gateway_transaction_id' => (string) $id]);
        }

        return $id === null ? null : (string) $id;
    }

    /**
     * Anula una transacción aprobada.
     *
     * Solo sirve mientras la transacción está aprobada y sin liquidar, así
     * que el cobro de verificación se anula en el mismo segundo. Pasado ese
     * momento Wompi responde «sólo las transacciones aprobadas pueden ser
     * potencialmente anuladas» y ya no hay vuelta atrás por aquí: habría que
     * pedir un reembolso, que tarda días.
     */
    public function voidTransaction(PaymentTransaction $transaction): bool
    {
        if (blank($transaction->gateway_transaction_id)) {
            return false;
        }

        try {
            // El cuerpo va explícito: `post()` sin datos manda `[]`, que es
            // un arreglo JSON, y Wompi lo rechaza con «el cuerpo del
            // mensaje no es un objeto JSON válido». Cuesta una tarde
            // averiguarlo.
            $response = Http::withToken((string) config('wompi.private_key'))
                ->withBody('{}', 'application/json')
                ->timeout(20)
                ->post($this->apiUrl().'/transactions/'.$transaction->gateway_transaction_id.'/void');
        } catch (\Throwable $e) {
            Log::error('No se pudo anular la transacción en Wompi', [
                'reference' => $transaction->reference,
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        // Wompi responde 201 también cuando se niega a anular, y el motivo
        // viene en el cuerpo: hay que mirarlo, no el código.
        $anulada = $response->successful()
            && strtoupper((string) $response->json('data.status')) === 'APPROVED'
            && $response->json('data.transaction') !== null;

        if (! $anulada) {
            Log::error('Wompi no anuló la transacción', [
                'reference' => $transaction->reference,
                'motivo' => $response->json('data.reason') ?? $response->body(),
            ]);

            return false;
        }

        $transaction->update([
            'status' => PaymentStatus::Voided,
            'voided_at' => now(),
            'paid_at' => null,
        ]);

        return true;
    }

    public function apiUrl(): string
    {
        return (string) config('wompi.urls.'.($this->isSandbox() ? 'sandbox' : 'production').'.api');
    }
}
