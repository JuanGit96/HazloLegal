<x-filament-panels::page>
    @unless ($lawyer?->is_published)
        <x-filament::section>
            <x-slot name="heading">Tu perfil todavía no es público</x-slot>

            <p class="hl-text">
                Puedes dejarlo listo desde ya. Se publica en el directorio cuando tu suscripción
                incluye perfil público y el equipo lo revisa.
            </p>
        </x-filament::section>
    @endunless

    <form wire:submit="save">
        {{ $this->form }}

        <div class="hl-row hl-row--end hl-mt-lg">
            <x-filament::button type="submit" icon="heroicon-o-check">
                Guardar cambios
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
