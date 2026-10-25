<?php

declare(strict_types=1);

namespace App\Filament\Pages\Roadmap;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class WhatsAppModule extends RoadmapPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    protected static ?string $navigationLabel = 'WhatsApp Business';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationBadge = 'Prometido';

    protected static ?string $title = 'WhatsApp Business API';

    public function summary(): string
    {
        return 'Enviar por WhatsApp lo que hoy solo sale por correo: confirmaciones, entregas, avisos de abogado asignado y recordatorios de firma.';
    }

    public function why(): string
    {
        return 'En Colombia el correo se lee tarde y WhatsApp se lee en minutos. Ya lo prometemos en la web («recibes el PDF en tu correo y tu WhatsApp»), así que hay que cumplirlo.';
    }

    public function priority(): string
    {
        return 'alta';
    }

    public function effort(): string
    {
        return '~2 semanas';
    }

    public function scope(): array
    {
        return [
            'Cuenta de WhatsApp Business API y **plantillas aprobadas por Meta** (las notificaciones salientes las exigen).',
            'Notificaciones al cliente: pedido recibido, pago confirmado, documento entregado, abogado asignado.',
            'Notificaciones al abogado: caso nuevo con botones de aceptar y rechazar, igual que en el correo.',
            'Recordatorios de firma pendiente.',
            'Bandeja de entrada en el panel para responder sin salir del sistema.',
            'Registro de la conversación asociada al pedido o al caso.',
        ];
    }

    public function alreadyInPlace(): array
    {
        return [
            '`WhatsAppLinkBuilder`, que ya genera los enlaces wa.me con mensaje prellenado.',
            'Botón flotante de WhatsApp en todo el sitio.',
            'Campos `whatsapp` y `notify_whatsapp` en la ficha de cada abogado.',
        ];
    }

    public function dependencies(): array
    {
        return ['Verificación del número y aprobación de plantillas por parte de Meta (toma días).'];
    }
}
