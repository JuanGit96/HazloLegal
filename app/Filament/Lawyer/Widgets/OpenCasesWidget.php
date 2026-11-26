<?php

declare(strict_types=1);

namespace App\Filament\Lawyer\Widgets;

use App\Domain\Lawyers\Services\CurrentLawyer;
use App\Domain\LegalCases\Enums\CaseOfferStatus;
use App\Domain\LegalCases\Models\CaseOffer;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Los casos abiertos a los que todavía se puede postular.
 *
 * Esto es lo primero que tiene que ver al entrar, y antes no estaba: el
 * portal solo mostraba los casos ya ganados, que es el final de la historia.
 * El reparto cambió —los abogados se postulan y elige el cliente—, así que
 * entrar al panel y encontrar «tienes 3 casos esperando» es la razón por la
 * que un abogado vuelve a abrirlo.
 *
 * El cliente no aparece: nombre, correo y teléfono solo existen para quien
 * sea elegido. Lo que se ve aquí es el caso, no la persona.
 */
class OpenCasesWidget extends TableWidget
{
    protected static ?int $sort = 0;

    // Sin carga diferida: es lo único sobre lo que el abogado puede actuar
    // al entrar, y un hueco gris donde debería estar «tienes 3 casos» no
    // cumple su función.
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Casos esperando tu postulación')
            ->description('De tu área. El cliente compara a los interesados y elige; responder pronto y con una buena nota es lo que inclina la balanza.')
            ->query($this->consulta())
            ->defaultSort('sent_at', 'desc')
            ->paginated([5, 10])
            ->columns([
                TextColumn::make('legalCase.practice_area')
                    ->label('Área')
                    ->badge(),

                TextColumn::make('legalCase.title')
                    ->label('El caso')
                    ->wrap()
                    ->limit(90)
                    ->description(fn (CaseOffer $record) => $record->legalCase?->client_city),

                TextColumn::make('legalCase.urgency')
                    ->label('Urgencia')
                    ->badge(),

                TextColumn::make('legalCase.expires_at')
                    ->label('Se cierra')
                    ->since()
                    ->placeholder('Sin plazo')
                    ->color(fn (CaseOffer $record) => $record->legalCase?->expires_at?->diffInHours(now()) > -12
                        ? 'danger'
                        : 'warning'),

                TextColumn::make('legalCase.interesados_count')
                    ->label('Interesados')
                    ->badge()
                    ->default(0)
                    // Saber que ya hay tres postulados cambia la decisión de
                    // postularse, y esconderlo sería hacerle perder el tiempo
                    // a quien llega tarde.
                    ->color(fn ($state) => (int) $state > 2 ? 'danger' : 'gray')
                    ->tooltip('Cuántos abogados se han postulado ya'),
            ])
            ->recordActions([
                Action::make('postularme')
                    ->label('Postularme')
                    ->icon('heroicon-o-hand-raised')
                    ->color('primary')
                    ->url(fn (CaseOffer $record) => $record->acceptUrl())
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('Ningún caso abierto ahora mismo')
            ->emptyStateDescription('Te avisamos por correo en cuanto entre uno de tu área. Si quieres que lleguen más, amplía tus áreas de práctica en «Mi perfil».')
            ->emptyStateIcon('heroicon-o-inbox');
    }

    private function consulta(): Builder
    {
        $lawyerId = app(CurrentLawyer::class)->id();

        return CaseOffer::query()
            // El conteo de interesados va en la misma consulta: pedirlo fila
            // a fila sería una consulta por caso, y con el modo estricto
            // puesto ni siquiera llegaría a ser lento, reventaría.
            ->with(['legalCase' => fn ($q) => $q->withCount([
                'offers as interesados_count' => fn (Builder $sub) => $sub->where('status', CaseOfferStatus::Accepted),
            ])])
            ->where('lawyer_id', $lawyerId)
            ->where('status', CaseOfferStatus::Pending)
            // Un caso ya asignado o vencido no es una oportunidad: ofrecerlo
            // sería hacerle perder el tiempo a quien pulse.
            ->whereHas('legalCase', fn (Builder $q) => $q
                ->whereNull('assigned_lawyer_id')
                ->where(fn (Builder $sub) => $sub->whereNull('expires_at')->orWhere('expires_at', '>', now())))
            ->when($lawyerId === null, fn (Builder $q) => $q->whereRaw('1 = 0'));
    }
}
