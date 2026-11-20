<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Orders\Models\Order;
use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\Payments\Services\PaymentProcessor;
use App\Domain\Payments\Services\WompiGateway;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Vuelta de Wompi: el webhook y el regreso del usuario.
 *
 * Quien manda es el webhook, no el navegador. El usuario puede cerrar la
 * pestaña antes de volver, o volver con la URL editada a mano; el estado
 * real se escribe cuando Wompi lo confirma con su firma.
 */
class WompiController
{
    public function __construct(
        private readonly WompiGateway $wompi,
        private readonly PaymentProcessor $processor,
    ) {}

    /** El webhook. Wompi lo llama cuando una transacción cambia de estado. */
    public function webhook(Request $request): JsonResponse
    {
        $event = $request->all();

        if (! $this->wompi->verifyEvent($event)) {
            Log::warning('Evento de Wompi con firma inválida', ['event' => data_get($event, 'event')]);

            return response()->json(['ok' => false], 401);
        }

        $data = data_get($event, 'data.transaction', []);
        $reference = data_get($data, 'reference');

        $transaction = PaymentTransaction::query()->where('reference', $reference)->first();

        if (! $transaction) {
            // Puede ser una transacción de otro entorno apuntando al mismo
            // webhook. Se responde 200 para que Wompi no reintente eternamente.
            Log::info('Evento de Wompi sin transacción local', ['reference' => $reference]);

            return response()->json(['ok' => true]);
        }

        $this->processor->apply($transaction, $data);

        return response()->json(['ok' => true]);
    }

    /**
     * El regreso del usuario desde el checkout.
     *
     * Se consulta el estado real a Wompi en vez de creerle a la URL: volver
     * con `?id=` no prueba nada.
     *
     * Nunca devuelve 404. Alguien que acaba de pagar y se encuentra un
     * «no encontrado» da por hecho que perdió su dinero, y eso no puede
     * pasar aunque el registro local se haya perdido.
     */
    public function returned(Request $request, string $reference): RedirectResponse|View
    {
        $transaction = PaymentTransaction::query()->where('reference', $reference)->first();

        $data = ($id = $request->query('id'))
            ? $this->wompi->fetch((string) $id)
            : null;

        if ($transaction) {
            if ($data) {
                $this->processor->apply($transaction, $data);
            }

            return $this->redirectFor($transaction->refresh());
        }

        // Sin registro local: puede ser otro entorno apuntando al mismo
        // dominio, o una base restaurada. Se intenta recuperar el pedido a
        // partir de la referencia, que lleva su identificador dentro.
        if ($order = $this->recoverOrder($reference)) {
            return redirect()->to($order->trackingUrl())->with(
                'status',
                $order->paid_at
                    ? '¡Pago confirmado!'
                    : 'Estamos confirmando tu pago con el banco. Te avisamos por correo.',
            );
        }

        Log::warning('Retorno de Wompi sin transacción local', [
            'reference' => $reference,
            'wompi_id' => $id,
            'wompi_status' => data_get($data, 'status'),
        ]);

        return view('wompi.pending', [
            'reference' => $reference,
            'status' => data_get($data, 'status'),
        ]);
    }

    /**
     * Recupera el pedido desde la referencia.
     *
     * La referencia se arma como `HL-{id}-{aleatorio}`, así que el pedido se
     * puede identificar aunque la fila de la transacción ya no esté.
     */
    private function recoverOrder(string $reference): ?Order
    {
        if (! preg_match('/^HL-(\d+)-/', $reference, $matches)) {
            return null;
        }

        return Order::query()->find((int) $matches[1]);
    }

    private function redirectFor(PaymentTransaction $transaction): RedirectResponse
    {
        $payable = $transaction->payable;

        if ($payable instanceof Order) {
            return $transaction->isApproved()
                ? redirect()->to($payable->refresh()->trackingUrl())->with('status', '¡Pago confirmado!')
                : redirect()->route('checkout.payment', $payable->reference)
                    ->with('paymentError', $transaction->status_message ?: 'El pago no se completó.');
        }

        return $transaction->isApproved()
            ? redirect()->route('lawyers.join')->with('status', 'Tu plan quedó activo. Revisa tu correo.')
            : redirect()->route('lawyers.join')->with('paymentError', 'El pago no se completó.');
    }
}
