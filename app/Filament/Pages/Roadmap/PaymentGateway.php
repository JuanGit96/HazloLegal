<?php

declare(strict_types=1);

namespace App\Filament\Pages\Roadmap;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class PaymentGateway extends RoadmapPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = 'Pasarela de pagos';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Pasarela de pagos real';

    public function summary(): string
    {
        return 'Reemplazar el pago simulado del prototipo por un cobro real con PSE, tarjeta, Nequi y Daviplata.';
    }

    public function why(): string
    {
        return 'Hoy el checkout registra el pedido pero no cobra. Sin cobro automático alguien tiene que verificar cada transferencia a mano, y eso no escala ni permite entregar en 4 horas.';
    }

    public function priority(): string
    {
        return 'alta';
    }

    public function effort(): string
    {
        return '~1,5 semanas';
    }

    public function scope(): array
    {
        return [
            'Integración con una pasarela colombiana (Wompi, ePayco, Mercado Pago o PayU).',
            '**Webhook de confirmación** que marque el pedido como pagado y arranque el reloj del plazo.',
            'Conciliación: comparar lo que dice la pasarela con lo que dice la base de datos.',
            'Manejo de pagos rechazados, pendientes y reversados.',
            'Devoluciones desde el panel, para las garantías que proceden.',
            'Recuperación de carritos abandonados: recordatorio a quien no completó el pago.',
            'Cupones y códigos de descuento para campañas.',
        ];
    }

    public function alreadyInPlace(): array
    {
        return [
            'Modelo de pedido con estado, medio de pago, referencia y fecha de pago.',
            '`OrderService::confirmPayment()`, que es el punto exacto donde entrará el webhook.',
            'Página de pago y de confirmación ya construidas.',
        ];
    }

    public function dependencies(): array
    {
        return ['Elegir pasarela y tener la cuenta de comercio aprobada.'];
    }
}
