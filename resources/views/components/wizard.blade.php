@props(['step' => 1])

@php
    $steps = [
        1 => 'Elige tu trámite',
        2 => 'Velocidad y datos',
        3 => 'Pago',
        4 => 'Listo',
    ];
@endphp

<ol class="wizard" aria-label="Progreso del pedido">
    @foreach ($steps as $number => $label)
        @php
            $state = $number < $step ? 'done' : ($number === $step ? 'current' : 'pending');
        @endphp
        <li class="wizard__step" data-state="{{ $state }}">
            <b aria-hidden="true">{{ $state === 'done' ? '✓' : $number }}</b>
            <span class="wizard__label">{{ $label }}</span>
            <span class="visually-hidden">
                {{ $state === 'done' ? '(completado)' : ($state === 'current' ? '(paso actual)' : '(pendiente)') }}
            </span>
        </li>
        @if (! $loop->last)
            <li class="wizard__sep" aria-hidden="true"></li>
        @endif
    @endforeach
</ol>
