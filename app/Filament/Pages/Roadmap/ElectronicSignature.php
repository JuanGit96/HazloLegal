<?php

declare(strict_types=1);

namespace App\Filament\Pages\Roadmap;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class ElectronicSignature extends RoadmapPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static ?string $navigationLabel = 'Firma electrónica (OTP)';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Firma electrónica con OTP';

    public function summary(): string
    {
        return 'Permitir que las partes firmen contratos desde el celular con un código de un solo uso, con el rastro de auditoría que exige la Ley 527 de 1999.';
    }

    public function why(): string
    {
        return 'Ya lo prometemos en la web y es un diferenciador real frente a quien solo manda un Word. Convierte los contratos de dos partes (arriendos, compraventas, NDA) en un producto cerrado de principio a fin.';
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
            'Invitación a firmar por correo y WhatsApp, con enlace único por firmante.',
            'Verificación de identidad con **OTP al celular** y confirmación del correo.',
            'Visor del documento antes de firmar, con registro de que se abrió.',
            '**Certificado de auditoría**: hora, IP, dispositivo, correo, celular y hash del documento.',
            'Orden de firmas cuando importa (primero arrendatario, luego codeudor, luego arrendador).',
            'Recordatorios automáticos a quien no ha firmado.',
            'PDF final con el certificado de auditoría anexo, enviado a todas las partes.',
            'Estado de firma visible en el panel y para el cliente.',
        ];
    }

    public function alreadyInPlace(): array
    {
        return [
            'Campo `supports_signature` por trámite y `wants_signature` por pedido.',
            'Página pública que explica la validez legal de la firma electrónica.',
        ];
    }

    public function dependencies(): array
    {
        return [
            'Generación de PDF, para tener el documento que se firma.',
            'Proveedor de SMS u OTP (o WhatsApp Business API).',
        ];
    }
}
