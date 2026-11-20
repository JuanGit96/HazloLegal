<x-filament-panels::page>
    @unless ($lawyer?->is_published)
        <x-filament::section>
            <x-slot name="heading">Tu perfil todavía no es público</x-slot>

            <p class="text-sm text-gray-600 dark:text-gray-400">
                Puedes dejarlo listo desde ya. Se publica en el directorio cuando tu suscripción
                incluye perfil público y el equipo lo revisa.
            </p>
        </x-filament::section>
    @endunless

    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex justify-end">
            <x-filament::button type="submit" icon="heroicon-o-check">
                Guardar cambios
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
