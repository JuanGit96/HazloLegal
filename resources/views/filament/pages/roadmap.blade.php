<x-filament-panels::page>
    @php
        $tone = match ($priority) {
            'alta' => ['bg' => 'danger', 'label' => 'Prioridad alta'],
            'baja' => ['bg' => 'gray', 'label' => 'Prioridad baja'],
            default => ['bg' => 'warning', 'label' => 'Prioridad media'],
        };
    @endphp

    <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="flex flex-wrap items-center gap-2">
            <x-filament::badge color="info">Pendiente de construir</x-filament::badge>
            <x-filament::badge :color="$tone['bg']">{{ $tone['label'] }}</x-filament::badge>
            <x-filament::badge color="gray">{{ $effort }}</x-filament::badge>
        </div>

        <p class="mt-4 text-base text-gray-700 dark:text-gray-300">{{ $summary }}</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 lg:col-span-2 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white">Qué debería hacer</h3>

            <ul class="mt-4 space-y-3">
                @foreach ($scope as $item)
                    <li class="flex gap-3 text-sm text-gray-700 dark:text-gray-300">
                        <span class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-primary-50 text-xs font-bold text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                            {{ $loop->iteration }}
                        </span>
                        <span>{!! \Illuminate\Support\Str::inlineMarkdown($item) !!}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="space-y-6">
            <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">Por qué importa</h3>
                <p class="mt-3 text-sm text-gray-700 dark:text-gray-300">{{ $why }}</p>
            </div>

            @if ($alreadyInPlace !== [])
                <div class="fi-section rounded-xl bg-success-50 p-6 ring-1 ring-success-600/20 dark:bg-success-500/10 dark:ring-success-400/20">
                    <h3 class="text-base font-semibold text-success-700 dark:text-success-400">Ya construido</h3>
                    <ul class="mt-3 space-y-2">
                        @foreach ($alreadyInPlace as $item)
                            <li class="flex gap-2 text-sm text-success-700 dark:text-success-300">
                                <span aria-hidden="true">✓</span>
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($dependencies !== [])
                <div class="fi-section rounded-xl bg-warning-50 p-6 ring-1 ring-warning-600/20 dark:bg-warning-500/10 dark:ring-warning-400/20">
                    <h3 class="text-base font-semibold text-warning-700 dark:text-warning-400">Depende de</h3>
                    <ul class="mt-3 space-y-2">
                        @foreach ($dependencies as $item)
                            <li class="flex gap-2 text-sm text-warning-700 dark:text-warning-300">
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
