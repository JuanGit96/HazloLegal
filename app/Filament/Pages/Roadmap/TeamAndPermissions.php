<?php

declare(strict_types=1);

namespace App\Filament\Pages\Roadmap;

use App\Filament\Support\NavGroup;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class TeamAndPermissions extends RoadmapPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::ROADMAP;

    protected static ?string $navigationLabel = 'Equipo y permisos';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Equipo, roles y permisos';

    public function summary(): string
    {
        return 'Separar quién puede ver y hacer qué dentro del panel: socios, abogados, auditores y soporte.';
    }

    public function why(): string
    {
        return 'Hoy cualquier usuario del panel ve todo, incluidos los datos sensibles de los casos de salud. Con más de dos personas en el equipo eso deja de ser aceptable, y en datos de salud es un riesgo frente a la Ley 1581.';
    }

    public function priority(): string
    {
        return 'alta';
    }

    public function effort(): string
    {
        return '~1 semana';
    }

    public function scope(): array
    {
        return [
            'Roles: **socio** (todo), **abogado** (sus casos), **auditor** (revisa documentos), **soporte** (pedidos sin datos sensibles), **marketing** (contenido y leads).',
            'Permisos por recurso y por acción, con `spatie/laravel-permission` o las políticas de Filament Shield.',
            'Ocultar la descripción de casos sensibles a quien no los atiende.',
            '**Registro de auditoría**: quién vio o cambió qué y cuándo. Es la prueba de diligencia frente a la SIC.',
            'Invitación de usuarios por correo y autenticación en dos pasos para los socios.',
            'Cierre de sesión automático por inactividad.',
        ];
    }

    public function alreadyInPlace(): array
    {
        return [
            'Autenticación del panel con usuarios de administración.',
            'Separación del modelo de abogado respecto del usuario del panel, que es justo lo que permite dar acceso diferenciado.',
        ];
    }

    public function dependencies(): array
    {
        return [];
    }
}
