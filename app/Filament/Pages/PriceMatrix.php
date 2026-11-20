<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Catalog\Models\ServiceAddon;
use App\Filament\Support\NavGroup;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

/**
 * El cruce de trámites × adicionales.
 *
 * Existe porque editar el precio de un adicional trámite por trámite es
 * entrar y salir de veinte pantallas, y así nadie revisa si los precios
 * guardan relación entre sí. Aquí se ve la tabla completa y se ajusta de
 * una sola vez.
 *
 * Una celda vacía significa «usa el precio por defecto del adicional»; un
 * 0 significa «incluido sin costo». Son cosas distintas y por eso no se
 * normalizan a lo mismo.
 */
class PriceMatrix extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::CATALOG;

    protected static ?string $navigationLabel = 'Matriz de precios';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Matriz de precios';

    protected string $view = 'filament.pages.price-matrix';

    /** @var array<int, array<int, array<string, mixed>>> [documentId][addonId] */
    public array $cells = [];

    public function mount(): void
    {
        $this->loadCells();
    }

    public function loadCells(): void
    {
        $this->cells = [];

        foreach ($this->documents() as $document) {
            foreach ($this->addons() as $addon) {
                $pivot = $document->addons->firstWhere('id', $addon->id)?->pivot;

                $this->cells[$document->id][$addon->id] = [
                    'attached' => $pivot !== null,
                    'price' => $pivot?->price !== null ? (string) $pivot->price : '',
                    'is_recommended' => (bool) $pivot?->is_recommended,
                    'is_preselected' => (bool) $pivot?->is_preselected,
                ];
            }
        }
    }

    /** @return Collection<int, LegalDocument> */
    public function documents(): Collection
    {
        return once(fn () => LegalDocument::query()
            ->with('addons')
            ->orderBy('practice_area')
            ->orderBy('sort_order')
            ->get());
    }

    /** @return Collection<int, ServiceAddon> */
    public function addons(): Collection
    {
        return once(fn () => ServiceAddon::query()->active()->get());
    }

    public function save(): void
    {
        foreach ($this->documents() as $document) {
            $sync = [];

            foreach ($this->addons() as $addon) {
                $cell = $this->cells[$document->id][$addon->id] ?? null;

                if (! $cell || ! ($cell['attached'] ?? false)) {
                    continue;
                }

                $price = trim((string) ($cell['price'] ?? ''));

                $sync[$addon->id] = [
                    'price' => $price === '' ? null : max(0, (int) $price),
                    'is_available' => true,
                    'is_recommended' => (bool) ($cell['is_recommended'] ?? false),
                    'is_preselected' => (bool) ($cell['is_preselected'] ?? false),
                ];
            }

            $document->addons()->sync($sync);
        }

        $this->loadCells();

        Notification::make()
            ->title('Matriz guardada')
            ->body('Los precios nuevos ya se aplican en el checkout.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar la matriz')
                ->icon(Heroicon::OutlinedCheck)
                ->action('save'),
        ];
    }

    /** Lo que se cobraría hoy por esa combinación. */
    public function effectivePrice(LegalDocument $document, ServiceAddon $addon): string
    {
        $cell = $this->cells[$document->id][$addon->id] ?? [];
        $price = trim((string) ($cell['price'] ?? ''));

        $amount = $price === '' ? (int) $addon->default_price : (int) $price;

        return $amount === 0 ? 'Incluido' : '$'.number_format($amount, 0, ',', '.');
    }
}
