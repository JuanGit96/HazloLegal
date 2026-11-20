{{--
    El cruce completo. Se dibuja a mano porque una tabla de Filament no
    sirve: aquí las columnas son registros de otra tabla, no campos.
--}}
<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Cómo leer esta tabla</x-slot>

        <ul class="text-sm space-y-1 text-gray-600 dark:text-gray-400">
            <li>• Marca la casilla de la esquina para <strong>ofrecer</strong> ese adicional en ese trámite.</li>
            <li>• <strong>Celda vacía</strong>: se cobra el precio por defecto del adicional.</li>
            <li>• <strong>0</strong>: se entrega incluido sin costo (así van los instructivos en los trámites de pago).</li>
            <li>• <strong>R</strong> lo destaca y lo usa como siguiente oferta; <strong>P</strong> lo deja premarcado.</li>
        </ul>
    </x-filament::section>

    <div class="overflow-x-auto rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10 bg-white dark:bg-gray-900">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-white/10">
                    <th class="sticky left-0 z-10 bg-white dark:bg-gray-900 px-4 py-3 text-left font-semibold">
                        Trámite
                    </th>
                    @foreach ($this->addons() as $addon)
                        <th class="px-3 py-3 text-center font-semibold whitespace-nowrap">
                            <span class="block">{{ $addon->emoji }} {{ $addon->short_name ?: $addon->name }}</span>
                            <span class="block text-xs font-normal text-gray-500">
                                {{ $addon->default_price === 0 ? 'incluido' : '$'.number_format($addon->default_price, 0, ',', '.') }}
                            </span>
                        </th>
                    @endforeach
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                @foreach ($this->documents() as $document)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <th scope="row" class="sticky left-0 z-10 bg-white dark:bg-gray-900 px-4 py-3 text-left font-medium">
                            <span class="block">{{ $document->short_name ?: $document->name }}</span>
                            <span class="block text-xs font-normal text-gray-500">
                                {{ $document->practice_area->getLabel() }} ·
                                {{ (int) $document->base_price === 0 ? 'gratis' : '$'.number_format($document->base_price, 0, ',', '.') }}
                                @unless ($document->self_service_enabled)
                                    · sin autoservicio
                                @endunless
                            </span>
                        </th>

                        @foreach ($this->addons() as $addon)
                            @php $cell = $this->cells[$document->id][$addon->id] ?? []; @endphp
                            <td class="px-3 py-3 align-top text-center">
                                <label class="flex items-center justify-center gap-1 text-xs text-gray-500">
                                    <input type="checkbox"
                                           wire:model.live="cells.{{ $document->id }}.{{ $addon->id }}.attached"
                                           class="rounded border-gray-300 text-primary-600">
                                    <span>ofrecer</span>
                                </label>

                                @if ($cell['attached'] ?? false)
                                    <input type="number"
                                           min="0"
                                           step="100"
                                           wire:model.live.debounce.400ms="cells.{{ $document->id }}.{{ $addon->id }}.price"
                                           placeholder="defecto"
                                           class="mt-2 w-24 rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 text-center text-sm">

                                    <span class="mt-1 block text-xs font-semibold text-primary-600">
                                        {{ $this->effectivePrice($document, $addon) }}
                                    </span>

                                    <span class="mt-1 flex items-center justify-center gap-2 text-xs text-gray-500">
                                        <label class="flex items-center gap-1" title="Recomendado">
                                            <input type="checkbox"
                                                   wire:model.live="cells.{{ $document->id }}.{{ $addon->id }}.is_recommended"
                                                   class="rounded border-gray-300 text-primary-600">
                                            R
                                        </label>
                                        <label class="flex items-center gap-1" title="Premarcado">
                                            <input type="checkbox"
                                                   wire:model.live="cells.{{ $document->id }}.{{ $addon->id }}.is_preselected"
                                                   class="rounded border-gray-300 text-primary-600">
                                            P
                                        </label>
                                    </span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex justify-end">
        <x-filament::button wire:click="save" icon="heroicon-o-check">
            Guardar la matriz
        </x-filament::button>
    </div>
</x-filament-panels::page>
