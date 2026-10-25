<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre')->searchable()->sortable()->weight('bold'),
                TextColumn::make('email')->label('Correo')->searchable()->copyable(),
                IconColumn::make('is_active')->label('Acceso')->boolean()->sortable(),
                TextColumn::make('created_at')->label('Creado')->dateTime('d M Y')->sortable()->toggleable(),
            ])
            ->recordActions([EditAction::make()->label('Editar')])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
