<?php

declare(strict_types=1);

namespace App\Filament\Resources\CookieConsents;

use App\Domain\Shared\Models\CookieConsent;
use App\Filament\Resources\CookieConsents\Pages\ListCookieConsents;
use App\Filament\Resources\CookieConsents\Tables\CookieConsentsTable;
use App\Filament\Support\NavGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;

/**
 * El registro de autorizaciones de cookies.
 *
 * Existe por una razón concreta: el Decreto 1377 de 2013 obliga al
 * responsable a conservar prueba de la autorización, y si la SIC la pide hay
 * que poder mostrarla. De solo lectura a propósito —una prueba que se puede
 * editar no es una prueba— y sin crear ni borrar: cada cambio de opinión del
 * visitante es una fila nueva.
 */
class CookieConsentResource extends Resource
{
    protected static ?string $model = CookieConsent::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::SETTINGS;

    protected static ?string $navigationLabel = 'Consentimiento de cookies';

    protected static ?int $navigationSort = 9;

    protected static ?string $modelLabel = 'autorización';

    protected static ?string $pluralModelLabel = 'autorizaciones de cookies';

    public static function table(Table $table): Table
    {
        return CookieConsentsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCookieConsents::route('/'),
        ];
    }
}
