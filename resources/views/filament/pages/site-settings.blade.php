<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="hl-row hl-row--end hl-mt-lg">
            <x-filament::button type="submit" icon="heroicon-o-check">
                Guardar ajustes
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
