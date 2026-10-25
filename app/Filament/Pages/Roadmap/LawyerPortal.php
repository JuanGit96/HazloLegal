<?php

declare(strict_types=1);

namespace App\Filament\Pages\Roadmap;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class LawyerPortal extends RoadmapPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?string $navigationLabel = 'Portal del abogado';

    protected static ?int $navigationSort = 7;

    protected static ?string $title = 'Portal del abogado';

    public function summary(): string
    {
        return 'Un área propia donde cada abogado de la red ve sus casos, sube documentos, registra horas y consulta lo que se le liquida.';
    }

    public function why(): string
    {
        return 'Hoy el abogado solo interactúa por correo. Cuando la red pase de seis a treinta abogados, coordinar por correo deja de funcionar y se vuelve el cuello de botella del negocio.';
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
            'Panel propio (segundo panel de Filament) con acceso solo a lo suyo.',
            'Bandeja de **casos invitados**, con aceptar y rechazar desde la interfaz además del correo.',
            'Mis casos activos, con estado y fechas comprometidas.',
            'Subida del documento terminado y envío a auditoría.',
            'Registro de horas y actuaciones por caso.',
            '**Liquidación de honorarios**: qué se le debe, qué ya se le pagó.',
            'Configuración de disponibilidad: pausar invitaciones durante vacaciones.',
            'Historial de desempeño: tasa de aceptación, tiempo de respuesta, calificación del cliente.',
        ];
    }

    public function alreadyInPlace(): array
    {
        return [
            'CRUD completo de abogados con áreas, cupo y estado.',
            'Modelo de invitaciones (`case_offers`) con token, estado y tiempos de respuesta.',
            'Aceptación y rechazo por enlace de correo, ya funcionando.',
        ];
    }

    public function dependencies(): array
    {
        return ['Módulo de equipo y permisos, para separar los roles de admin y abogado.'];
    }
}
