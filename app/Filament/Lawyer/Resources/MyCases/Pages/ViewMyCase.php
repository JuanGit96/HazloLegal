<?php

declare(strict_types=1);

namespace App\Filament\Lawyer\Resources\MyCases\Pages;

use App\Domain\LegalCases\Models\LegalCase;
use App\Filament\Lawyer\Resources\MyCases\MyCaseResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewMyCase extends ViewRecord
{
    protected static string $resource = MyCaseResource::class;

    public function getTitle(): string
    {
        return $this->record->reference;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('El caso')
                ->columns(2)
                ->schema([
                    TextEntry::make('title')->label('Título')->columnSpanFull(),
                    TextEntry::make('practice_area')->label('Área')->badge(),
                    TextEntry::make('urgency')->label('Urgencia')->badge(),
                    TextEntry::make('status')->label('Estado')->badge(),
                    TextEntry::make('budget_range')->label('Presupuesto')->badge()->placeholder('No lo indicó'),
                    TextEntry::make('description')
                        ->label('Lo que contó el cliente')
                        ->prose()
                        ->columnSpanFull(),
                    TextEntry::make('deadline_note')
                        ->label('Plazo que mencionó')
                        ->placeholder('Ninguno')
                        ->columnSpanFull(),
                ]),

            Section::make('Contacto del cliente')
                ->description('Estos datos son tuyos porque aceptaste el caso primero. Ningún otro abogado de la red los ve.')
                ->columns(2)
                ->schema([
                    TextEntry::make('client_name')->label('Nombre'),
                    TextEntry::make('client_email')->label('Correo')->copyable(),
                    TextEntry::make('client_phone')->label('Celular')->copyable(),
                    TextEntry::make('client_city')->label('Ciudad')->placeholder('—'),
                    TextEntry::make('assigned_at')
                        ->label('Lo tomaste el')
                        ->dateTime('l j \d\e F, g:i a')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    protected function resolveRecord(int|string $key): LegalCase
    {
        // Doble cierre: la consulta ya está acotada, y aquí se vuelve a
        // comprobar para que una URL adivinada tampoco abra el caso de otro.
        return static::getResource()::getEloquentQuery()->findOrFail($key);
    }
}
