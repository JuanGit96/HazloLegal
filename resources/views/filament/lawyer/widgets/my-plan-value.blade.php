<x-filament-widgets::widget>
    <x-filament::section
        icon="heroicon-o-sparkles"
        icon-color="primary">
        <x-slot name="heading">Lo que te ha dado tu plan</x-slot>
        <x-slot name="description">
            @if ($this->since())
                Desde {{ $this->since() }}. Son hechos contados, no estimaciones.
            @else
                Son hechos contados, no estimaciones.
            @endif
        </x-slot>

        <dl class="hl-stats">
            @foreach ($this->lines() as $line)
                <div class="hl-stat">
                    <span @class(['hl-stat-mark', 'is-off' => $line['value'] === 0])>
                        <x-filament::icon :icon="$line['kind']->getIcon()" />
                    </span>

                    <div class="hl-stat-body">
                        <dt class="hl-stat-label">{{ $line['kind']->getLabel() }}</dt>
                        <dd class="hl-stat-value">{{ number_format($line['value'], 0, ',', '.') }}</dd>
                        <p class="hl-stat-hint">
                            {{-- Un cero sin explicación solo frustra; con el
                                 siguiente paso al lado, es una tarea. --}}
                            {{ $line['hint'] ?? $line['kind']->explanation() }}
                        </p>
                    </div>
                </div>
            @endforeach
        </dl>
    </x-filament::section>
</x-filament-widgets::widget>
