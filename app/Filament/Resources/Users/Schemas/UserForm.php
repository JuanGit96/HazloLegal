<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(120),

                    TextInput::make('email')
                        ->label('Correo')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true),

                    TextInput::make('password')
                        ->label('Contraseña')
                        ->password()
                        ->revealable()
                        ->minLength(10)
                        ->dehydrateStateUsing(fn (?string $state) => filled($state) ? Hash::make($state) : null)
                        ->dehydrated(fn (?string $state) => filled($state))
                        ->required(fn (string $operation) => $operation === 'create')
                        ->helperText('Al editar, déjala vacía para no cambiarla. Mínimo 10 caracteres.')
                        ->columnSpanFull(),
                ]),

            Section::make('Acceso')
                ->description('Por ahora todos los usuarios activos tienen acceso completo al panel. La separación por roles está planeada en «Equipo y permisos» de la hoja de ruta.')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Puede entrar al panel')
                        ->default(true)
                        ->inline(false)
                        ->helperText('Desactivarlo bloquea el acceso sin borrar la cuenta ni su historial.'),
                ]),
        ]);
    }
}
