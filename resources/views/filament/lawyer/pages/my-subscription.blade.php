<x-filament-panels::page>
    @php $subscription = $this->subscription(); @endphp

    @if (! $subscription)
        <x-filament::section>
            <x-slot name="heading">Todavía no tienes un plan</x-slot>

            <p class="hl-text">
                Tu cuenta existe, pero sin plan no apareces en el directorio ni recibes casos.
            </p>

            <div class="hl-row hl-mt">
                @foreach (\App\Domain\Subscriptions\Models\LawyerPlan::query()->active()->orderBy('sort_order')->get() as $plan)
                    <x-filament::button
                        tag="a"
                        :color="$plan->is_highlighted ? 'primary' : 'gray'"
                        href="{{ route('subscription.checkout', ['plan' => $plan->id, 'periodo' => 'monthly']) }}">
                        {{ $plan->name }} · ${{ number_format($plan->price_monthly, 0, ',', '.') }}/mes
                    </x-filament::button>
                @endforeach
            </div>
        </x-filament::section>
    @else
        <x-filament::section>
            <x-slot name="heading">{{ $subscription->plan?->name }}</x-slot>
            <x-slot name="description">{{ $subscription->plan?->tagline }}</x-slot>

            <dl class="hl-facts">
                <div>
                    <dt>Estado</dt>
                    <dd>{{ $subscription->status?->getLabel() }}</dd>
                </div>
                <div>
                    <dt>Pagas</dt>
                    <dd>
                        ${{ number_format($subscription->price, 0, ',', '.') }}
                        · {{ $subscription->billing_period?->getLabel() }}
                    </dd>
                </div>
                <div>
                    <dt>{{ $subscription->trial_ends_at ? 'Termina la prueba' : 'Renueva' }}</dt>
                    <dd>
                        {{ ($subscription->trial_ends_at ?? $subscription->renews_at)?->translatedFormat('j \d\e F \d\e Y') ?? '—' }}
                    </dd>
                </div>
            </dl>

            {{-- Si ya pidió un cambio, eso manda sobre todo lo demás de
                 esta pantalla: lo primero que tiene que ver es qué pasa y
                 cuándo. --}}
            @if ($subscription->isEnding())
                <p class="hl-text hl-text--warn hl-mt">
                    <strong>Tu plan termina el {{ $subscription->ends_at?->translatedFormat('j \d\e F \d\e Y') }}.</strong>
                    Hasta ese día conservas todo. No te vamos a cobrar de nuevo.
                    <a href="{{ \App\Filament\Lawyer\Pages\CancelSubscription::getUrl() }}" class="hl-link">Seguir con mi plan</a>.
                </p>
            @elseif ($subscription->hasScheduledPlan())
                <p class="hl-text hl-text--accent hl-mt">
                    <strong>El {{ $subscription->renews_at?->translatedFormat('j \d\e F \d\e Y') }} pasas al plan {{ $subscription->scheduledPlan?->name }}.</strong>
                    Hasta ese día sigues con todo lo de {{ $subscription->plan?->name }}.
                    <a href="{{ \App\Filament\Lawyer\Pages\CancelSubscription::getUrl() }}" class="hl-link">Deshacer</a>.
                </p>
            @endif

            @if ($subscription->trialDaysLeft() !== null)
                <p class="hl-text hl-text--accent hl-mt">
                    Te quedan {{ $subscription->trialDaysLeft() }} días sin costo. El
                    {{ $subscription->trial_ends_at?->translatedFormat('j \d\e F') }} se cobra
                    ${{ number_format($subscription->nextChargeAmount(), 0, ',', '.') }}
                    @if ($subscription->hasStoredCard())
                        en tu {{ $subscription->cardLabel() }}.
                    @else
                        y todavía no tienes una tarjeta registrada.
                    @endif
                </p>
            @endif

            {{-- La tarjeta y el próximo cobro, siempre a la vista. Un cargo
                 que el abogado no esperaba es una disputa con el banco, y eso
                 cuesta más que el mes que se cobró. --}}
            <dl class="hl-facts hl-mt">
                <div>
                    <dt>Tarjeta registrada</dt>
                    <dd>
                        @if ($subscription->hasStoredCard())
                            {{ $subscription->cardLabel() }}
                            <span class="hl-note">
                                Comprobada y autorizada el
                                {{ $subscription->recurring_authorized_at?->translatedFormat('j \d\e F \d\e Y') }}.
                            </span>
                        @elseif (filled($subscription->payment_source_id))
                            {{ $subscription->cardLabel() ?: 'Tu tarjeta' }}
                            <span class="hl-note">
                                Estamos esperando la respuesta de tu banco. Te escribimos en cuanto
                                conteste; hasta entonces el plan no arranca.
                            </span>
                        @else
                            Ninguna
                            <span class="hl-note">
                                Sin tarjeta comprobada no podemos cobrar la renovación.
                            </span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt>Próximo cobro</dt>
                    <dd>
                        {{-- Cuando el cobro pasa por la pasarela, aquí va la
                             cifra que la pasarela va a cobrar. Si la pantalla
                             dice una y dos clics después aparece otra, lo que
                             se pierde es la confianza, aunque cambie a favor
                             del abogado. --}}
                        @if ($oferta = $this->mustCheckout() ? $this->offerFor($subscription->plan) : null)
                            {{ $oferta->formattedDueToday() }}
                            <span class="hl-price-was">{{ $oferta->formattedListPrice() }}</span>
                            <span class="hl-note">
                                {{ $oferta->headline() }}. Después,
                                {{ $oferta->formattedRecurringPrice() }} por {{ $oferta->periodLabel() }}@if ($oferta->fullPriceFrom), desde el {{ $oferta->fullPriceFrom->translatedFormat('j \d\e F \d\e Y') }}@endif.
                            </span>
                        @else
                            ${{ number_format($subscription->nextChargeAmount(), 0, ',', '.') }}
                            @if ($subscription->isInDiscountWindow())
                                <span class="hl-note">
                                    Precio promocional hasta el
                                    {{ $subscription->discount_ends_at?->translatedFormat('j \d\e F \d\e Y') }};
                                    después, ${{ number_format($subscription->plan?->priceFor($subscription->billing_period) ?? 0, 0, ',', '.') }}.
                                </span>
                            @elseif ($subscription->promo_code)
                                <span class="hl-note">
                                    Entraste con la invitación {{ $subscription->promo_code }}.
                                </span>
                            @endif
                        @endif
                    </dd>
                </div>
            </dl>

            {{-- El botón solo cuando hace falta la mano del abogado. Con
                 tarjeta comprobada y la suscripción al día, el cobro sale
                 solo; un botón de «renovar» ahí haría pensar que hay algo
                 pendiente que no existe. --}}
            @if ($this->mustCheckout())
                <div class="hl-row hl-mt-lg">
                    <x-filament::button
                        tag="a"
                        href="{{ route('subscription.checkout', ['plan' => $subscription->plan->id, 'periodo' => $this->period()->value]) }}"
                        icon="heroicon-o-credit-card">
                        {{ $subscription->status?->value === 'past_due' ? 'Activar mi plan' : 'Renovar mi plan' }}
                    </x-filament::button>
                </div>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Qué incluye tu plan</x-slot>

            <ul class="hl-list">
                @foreach ($this->features() as $feature)
                    <li>
                        <span @class(['hl-check', 'is-on' => $feature['included'], 'is-off' => ! $feature['included']])>
                            {{ $feature['included'] ? '✓' : '—' }}
                        </span>

                        <span>
                            <span @class(['hl-feature-title', 'is-off' => ! $feature['included']])>
                                {{ $feature['title'] }}
                            </span>
                            <span class="hl-feature-desc">{{ $feature['description'] }}</span>
                        </span>
                    </li>
                @endforeach
            </ul>
        </x-filament::section>

        @if ($this->upgrades()->isNotEmpty())
            <x-filament::section>
                <x-slot name="heading">Si necesitas más</x-slot>

                <div class="hl-grid hl-grid--2">
                    @foreach ($this->upgrades() as $plan)
                        @php $oferta = $this->offerFor($plan); @endphp
                        <div class="hl-card">
                            <p class="hl-card-title">{{ $plan->name }}</p>
                            <p class="hl-card-desc">{{ $plan->tagline }}</p>

                            @if ($oferta)
                                <p class="hl-price">
                                    {{ $oferta->formattedDueToday() }}
                                    <span class="hl-price-was">{{ $oferta->formattedListPrice() }}</span>
                                </p>
                                <p class="hl-note">
                                    {{ $oferta->headline() }}. Después,
                                    {{ $oferta->formattedRecurringPrice() }} por {{ $oferta->periodLabel() }}@if ($oferta->fullPriceFrom), desde el {{ $oferta->fullPriceFrom->translatedFormat('j \d\e F \d\e Y') }}@endif.
                                </p>
                            @else
                                <p class="hl-price">
                                    ${{ number_format($plan->priceFor($this->period()), 0, ',', '.') }} / {{ $this->periodLabel() }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="hl-row hl-mt">
                    @foreach ($this->upgrades() as $plan)
                        <x-filament::button
                            tag="a"
                            color="gray"
                            href="{{ route('subscription.checkout', ['plan' => $plan->id, 'periodo' => $this->period()->value]) }}">
                            Cambiarme a {{ $plan->name }}
                        </x-filament::button>
                    @endforeach
                </div>
            </x-filament::section>
        @endif
        {{-- La salida va al final y sin botón. Quien la busca la encuentra;
             a quien no la buscaba, ofrecerle un plan más barato en la
             pantalla de su cuenta solo le planta la idea. --}}
        <p class="hl-text hl-quiet">
            ¿Necesitas cambiar de plan o cancelar?
            <a href="{{ \App\Filament\Lawyer\Pages\CancelSubscription::getUrl() }}" class="hl-link">Mira tus opciones</a>.
        </p>
    @endif
</x-filament-panels::page>
