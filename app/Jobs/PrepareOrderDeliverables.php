<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Services\OrderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Prepara y entrega los archivos de un pedido.
 *
 * Se encola porque la entrega estándar es a 24 horas: generar el PDF en el
 * acto y mandarlo igual haría que la entrega inmediata no valiera nada, y
 * quien la pagó tiene derecho a que signifique algo.
 *
 * Cuando el pedido lleva entrega inmediata, esto corre sin retraso.
 */
class PrepareOrderDeliverables implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 120;

    public function __construct(public readonly int $orderId) {}

    public function handle(OrderService $orders): void
    {
        $order = Order::query()->with(['document', 'addons'])->find($this->orderId);

        if (! $order || $order->paid_at === null) {
            return;
        }

        // Si alguien ya lo generó a mano desde el panel, no se rehace.
        if ($order->document_path !== null) {
            return;
        }

        $orders->deliver($order);
    }

    /** Para no encolar dos veces el mismo pedido. */
    public function uniqueId(): string
    {
        return (string) $this->orderId;
    }
}
