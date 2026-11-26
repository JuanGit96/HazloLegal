<x-filament-panels::page>
    @php
        $subscription = $this->subscription();
        $cuando = $this->changesOn();
    @endphp

    @if (! $subscription)
        <x-filament::section>
            <x-slot name="heading">No tienes un plan activo</x-slot>
            <p class="hl-text">No hay nada que cancelar.</p>
        </x-filament::section>

    @elseif ($subscription->isEnding() || $subscription->hasScheduledPlan())
        {{-- Ya decidió. Lo único que falta aquí es poder volver atrás. --}}
        <x-filament::section icon="heroicon-o-clock" icon-color="warning">
            <x-slot name="heading">
                {{ $subscription->isEnding()
                    ? 'Tu plan termina el '.$cuando
                    : 'El '.$cuando.' pasas a '.$subscription->scheduledPlan?->name }}
            </x-slot>
            <x-slot name="description">Hasta ese día conservas todo lo de tu plan {{ $subscription->plan?->name }}.</x-slot>

            <p class="hl-text">
                @if ($subscription->isEnding())
                    No te vamos a cobrar de nuevo. Tus guías se quedan publicadas con tu firma;
                    lo que desaparece ese día es tu ficha del directorio.
                @else
                    Seguirás pagando
                    ${{ number_format($subscription->scheduledPlan?->priceFor($subscription->billing_period) ?? 0, 0, ',', '.') }}
                    por {{ $subscription->billing_period?->getLabel() === 'Anual' ? 'año' : 'mes' }} desde esa fecha.
                @endif
            </p>

            <div class="hl-row hl-mt-lg">
                <x-filament::button wire:click="keepEverything" icon="heroicon-o-arrow-uturn-left">
                    Seguir con mi plan {{ $subscription->plan?->name }}
                </x-filament::button>
            </div>
        </x-filament::section>

    @elseif ($plan = $this->chosenPlan())
        @php $cambio = $this->comparison($plan); @endphp

        <x-filament::section icon="heroicon-o-arrow-trending-down" icon-color="warning">
            <x-slot name="heading">Pasarte a {{ $plan->name }} el {{ $cuando }}</x-slot>
            <x-slot name="description">
                Hoy no cambia nada. Hasta esa fecha sigues con todo lo de {{ $subscription->plan?->name }}.
            </x-slot>

            <dl class="hl-facts">
                <div>
                    <dt>Pagas hoy</dt>
                    <dd>${{ number_format($subscription->price, 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt>Pagarás desde el {{ $cuando }}</dt>
                    <dd>${{ number_format($plan->priceFor($subscription->billing_period), 0, ',', '.') }}</dd>
                </div>
            </dl>

            @if ($cambio['pierde'] !== [])
                <h3 class="hl-h3 hl-mt-lg">Lo que dejas de tener ese día</h3>
                <ul class="hl-ticks hl-ticks--loses">
                    @foreach ($cambio['pierde'] as $item)
                        <li><span aria-hidden="true">−</span><span>{{ $item }}</span></li>
                    @endforeach
                </ul>
            @endif

            @if ($cambio['conserva'] !== [])
                <h3 class="hl-h3 hl-mt-lg">Lo que conservas</h3>
                <ul class="hl-ticks hl-ticks--keeps">
                    @foreach ($cambio['conserva'] as $item)
                        <li><span aria-hidden="true">✓</span><span>{{ $item }}</span></li>
                    @endforeach
                </ul>
            @endif

            <div class="hl-row hl-mt-lg">
                <x-filament::button wire:click="confirmChange" icon="heroicon-o-check">
                    Sí, pasarme a {{ $plan->name }}
                </x-filament::button>
                <x-filament::button wire:click="back" color="gray">
                    Volver
                </x-filament::button>
            </div>
        </x-filament::section>

    @elseif ($cancelando)
        <x-filament::section icon="heroicon-o-hand-raised" icon-color="danger">
            <x-slot name="heading">Cancelar mi suscripción</x-slot>
            <x-slot name="description">
                Conservas todo hasta el {{ $cuando }}. Ese día dejamos de cobrarte.
            </x-slot>

            {{-- Saber por qué se va es lo único que una baja deja de útil. --}}
            <label class="hl-label" for="motivo">¿Por qué te vas?</label>
            <select id="motivo" class="hl-select" wire:model.live="motivo">
                <option value="">Elige una razón</option>
                @foreach (\App\Filament\Lawyer\Pages\CancelSubscription::MOTIVOS as $clave => $texto)
                    <option value="{{ $clave }}">{{ $texto }}</option>
                @endforeach
            </select>

            <p class="hl-text hl-mt-lg">
                <strong>Tus guías se quedan publicadas, con tu firma.</strong>
                Lo que escribiste no se baja del sitio. Lo que desaparece el {{ $cuando }} es tu
                ficha del directorio y el enlace para escribirte desde esas guías.
            </p>

            <div class="hl-row hl-mt-lg">
                <x-filament::button
                    wire:click="confirmCancellation"
                    color="danger"
                    icon="heroicon-o-x-mark"
                    :disabled="blank($motivo)">
                    Cancelar el {{ $cuando }}
                </x-filament::button>
                <x-filament::button wire:click="back" color="gray">
                    Volver
                </x-filament::button>
            </div>
        </x-filament::section>

    @else
        <x-filament::section icon="heroicon-o-chart-bar" icon-color="primary">
            <x-slot name="heading">Esto es lo que te pasó en los últimos 30 días</x-slot>
            <x-slot name="description">Tus cifras, para que decidas con ellas y no de memoria.</x-slot>

            <dl class="hl-kpis">
                @foreach ($this->numbers() as $dato)
                    <div>
                        <dd class="hl-kpi-value">{{ number_format($dato['value'], 0, ',', '.') }}</dd>
                        <dt class="hl-kpi-label">{{ $dato['label'] }}</dt>
                    </div>
                @endforeach
            </dl>
        </x-filament::section>

        @if ($this->cheaperPlans()->isNotEmpty())
            <x-filament::section>
                <x-slot name="heading">Si es por el precio, hay planes más baratos</x-slot>
                <x-slot name="description">
                    El cambio entra el {{ $cuando }}. Hasta ese día no pierdes nada de lo que ya pagaste.
                </x-slot>

                <div class="hl-grid hl-grid--2">
                    @foreach ($this->cheaperPlans() as $barato)
                        @php $cambio = $this->comparison($barato); @endphp
                        <div class="hl-card">
                            <p class="hl-card-title">{{ $barato->name }}</p>
                            <p class="hl-price">
                                ${{ number_format($barato->priceFor($subscription->billing_period), 0, ',', '.') }}
                                <span class="hl-price-was">${{ number_format($subscription->price, 0, ',', '.') }}</span>
                            </p>
                            <p class="hl-card-desc">{{ $barato->tagline }}</p>

                            @if ($cambio['pierde'] !== [])
                                <p class="hl-note">Dejas de tener: {{ implode(' · ', $cambio['pierde']) }}.</p>
                            @endif

                            <div class="hl-row hl-mt">
                                <x-filament::button wire:click="choose({{ $barato->id }})" color="gray" size="sm">
                                    Pasarme a {{ $barato->name }}
                                </x-filament::button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endif

        <x-filament::section>
            <x-slot name="heading">Lo que se queda aunque te vayas</x-slot>

            <p class="hl-text">
                <strong>Tus guías siguen publicadas, con tu firma.</strong>
                Lo que escribiste para HazloLegal no se baja del sitio: sigue indexado y sigue
                llevando tu nombre. Lo que desaparece es tu ficha del directorio y el enlace
                para escribirte desde esas guías.
            </p>

            <div class="hl-row hl-mt-lg">
                <x-filament::button wire:click="startCancelling" color="gray" icon="heroicon-o-x-mark">
                    Cancelar de todas formas
                </x-filament::button>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
