<?php

declare(strict_types=1);

namespace App\Filament\Pages\Roadmap;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class LawyerPortal extends RoadmapPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?string $navigationLabel = 'Portal del abogado (fase 2)';

    protected static ?int $navigationSort = 7;

    protected static ?string $title = 'Portal del abogado';

    public function summary(): string
    {
        return 'Lo que le falta al portal que ya existe en /portal: subir el documento terminado, registrar horas y consultar la liquidación de honorarios.';
    }

    public function why(): string
    {
        return 'El portal ya resuelve lo que sostiene la suscripción: el abogado ve sus casos, sus contactos y sus resultados. Lo que falta es la parte operativa del trabajo en sí, que hoy se coordina por correo y deja de escalar cuando la red pase de seis a treinta abogados.';
    }

    public function priority(): string
    {
        return 'media';
    }

    public function effort(): string
    {
        return '~3 semanas';
    }

    public function scope(): array
    {
        return [
            'Bandeja de **casos invitados**, con aceptar y rechazar desde la interfaz además del correo.',
            'Subida del documento terminado y envío a revisión.',
            'Registro de horas y actuaciones por caso.',
            '**Liquidación de honorarios**: qué se le debe, qué ya se le pagó.',
            'Configuración de disponibilidad: pausar invitaciones durante vacaciones.',
            'Marcar un contacto como contactado o descartado, para que el siguiente abogado no repita la llamada.',
            'Pago de la suscripción desde el propio portal, sin escribirle al equipo.',
        ];
    }

    public function alreadyInPlace(): array
    {
        return [
            '**Portal propio en `/portal`**, panel aparte del de administración.',
            'Mis casos: solo los que ganó, con los datos del cliente que ningún otro abogado ve.',
            'Contactos de sus áreas que autorizaron ser contactados, con registro de cada apertura.',
            'Perfil público editable por él mismo, sin tocar la tarjeta profesional.',
            'Su suscripción con lo que incluye y lo que no.',
            'Resumen con visitas al perfil, clics de contacto y casos tomados.',
            'Aceptación y rechazo de casos por enlace de correo.',
        ];
    }

    public function dependencies(): array
    {
        return ['Pasarela de pagos, para cobrar la suscripción sin intervención manual.'];
    }
}
