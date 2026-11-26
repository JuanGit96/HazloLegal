<x-filament-panels::page>
    @php
        $tone = match ($priority) {
            'alta' => ['bg' => 'danger', 'label' => 'Prioridad alta'],
            'baja' => ['bg' => 'gray', 'label' => 'Prioridad baja'],
            default => ['bg' => 'warning', 'label' => 'Prioridad media'],
        };
    @endphp

    <div class="hl-box">
        <div class="hl-row hl-row--tight">
            <x-filament::badge color="info">Pendiente de construir</x-filament::badge>
            <x-filament::badge :color="$tone['bg']">{{ $tone['label'] }}</x-filament::badge>
            <x-filament::badge color="gray">{{ $effort }}</x-filament::badge>
        </div>

        <p class="hl-text hl-mt">{{ $summary }}</p>
    </div>

    <div class="hl-grid hl-grid--sidebar">
        <div class="hl-box">
            <h3 class="hl-h3">Qué debería hacer</h3>

            <ul class="hl-steps">
                @foreach ($scope as $item)
                    <li>
                        <span class="hl-step-n">{{ $loop->iteration }}</span>
                        <span>{!! \Illuminate\Support\Str::inlineMarkdown($item) !!}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="hl-stack">
            <div class="hl-box">
                <h3 class="hl-h3">Por qué importa</h3>
                <p class="hl-text hl-mt">{{ $why }}</p>
            </div>

            @if ($alreadyInPlace !== [])
                <div class="hl-box hl-box--success">
                    <h3 class="hl-h3">Ya construido</h3>
                    <ul class="hl-ticks">
                        @foreach ($alreadyInPlace as $item)
                            <li>
                                <span aria-hidden="true">✓</span>
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($dependencies !== [])
                <div class="hl-box hl-box--warning">
                    <h3 class="hl-h3">Depende de</h3>
                    <ul class="hl-ticks">
                        @foreach ($dependencies as $item)
                            <li>
                                <span aria-hidden="true">→</span>
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
