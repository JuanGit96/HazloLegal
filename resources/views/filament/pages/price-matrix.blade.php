{{--
    El cruce completo. Se dibuja a mano porque una tabla de Filament no
    sirve: aquí las columnas son registros de otra tabla, no campos.
--}}
<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Cómo leer esta tabla</x-slot>

        <ul class="hl-bullets">
            <li>• Marca la casilla de la esquina para <strong>ofrecer</strong> ese adicional en ese trámite.</li>
            <li>• <strong>Celda vacía</strong>: se cobra el precio por defecto del adicional.</li>
            <li>• <strong>0</strong>: se entrega incluido sin costo (así van los instructivos en los trámites de pago).</li>
            <li>• <strong>R</strong> lo destaca y lo usa como siguiente oferta; <strong>P</strong> lo deja premarcado.</li>
        </ul>
    </x-filament::section>

    <div class="hl-table-wrap">
        <table class="hl-table">
            <thead>
                <tr>
                    <th class="hl-sticky">Trámite</th>
                    @foreach ($this->addons() as $addon)
                        <th>
                            <span>{{ $addon->emoji }} {{ $addon->short_name ?: $addon->name }}</span>
                            <span class="hl-note">
                                {{ $addon->default_price === 0 ? 'incluido' : '$'.number_format($addon->default_price, 0, ',', '.') }}
                            </span>
                        </th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @foreach ($this->documents() as $document)
                    <tr>
                        <th scope="row" class="hl-sticky">
                            <span>{{ $document->short_name ?: $document->name }}</span>
                            <span class="hl-note">
                                {{ $document->practice_area->getLabel() }} ·
                                {{ (int) $document->base_price === 0 ? 'gratis' : '$'.number_format($document->base_price, 0, ',', '.') }}
                                @unless ($document->self_service_enabled)
                                    · sin autoservicio
                                @endunless
                            </span>
                        </th>

                        @foreach ($this->addons() as $addon)
                            @php $cell = $this->cells[$document->id][$addon->id] ?? []; @endphp
                            <td class="hl-cell">
                                <label class="hl-inline">
                                    <input type="checkbox"
                                           wire:model.live="cells.{{ $document->id }}.{{ $addon->id }}.attached">
                                    <span>ofrecer</span>
                                </label>

                                @if ($cell['attached'] ?? false)
                                    <input type="number"
                                           min="0"
                                           step="100"
                                           wire:model.live.debounce.400ms="cells.{{ $document->id }}.{{ $addon->id }}.price"
                                           placeholder="defecto"
                                           class="hl-number">

                                    <span class="hl-cell-price">{{ $this->effectivePrice($document, $addon) }}</span>

                                    <span class="hl-inline-group">
                                        <label class="hl-inline" title="Recomendado">
                                            <input type="checkbox"
                                                   wire:model.live="cells.{{ $document->id }}.{{ $addon->id }}.is_recommended">
                                            R
                                        </label>
                                        <label class="hl-inline" title="Premarcado">
                                            <input type="checkbox"
                                                   wire:model.live="cells.{{ $document->id }}.{{ $addon->id }}.is_preselected">
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

    <div class="hl-row hl-row--end">
        <x-filament::button wire:click="save" icon="heroicon-o-check">
            Guardar la matriz
        </x-filament::button>
    </div>
</x-filament-panels::page>
