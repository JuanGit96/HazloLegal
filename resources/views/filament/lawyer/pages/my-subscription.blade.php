<x-filament-panels::page>
    @php $subscription = $this->subscription(); @endphp

    @if (! $subscription)
        <x-filament::section>
            <x-slot name="heading">Todavía no tienes un plan</x-slot>

            <p class="text-sm text-gray-600 dark:text-gray-400">
                Tu cuenta existe, pero sin plan no apareces en el directorio ni recibes casos.
            </p>

            <div class="mt-4 flex flex-wrap gap-3">
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

            <dl class="grid gap-4 sm:grid-cols-3 text-sm">
                <div>
                    <dt class="text-gray-500">Estado</dt>
                    <dd class="font-semibold">{{ $subscription->status?->getLabel() }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Pagas</dt>
                    <dd class="font-semibold">
                        ${{ number_format($subscription->price, 0, ',', '.') }}
                        · {{ $subscription->billing_period?->getLabel() }}
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500">
                        {{ $subscription->trial_ends_at ? 'Termina la prueba' : 'Renueva' }}
                    </dt>
                    <dd class="font-semibold">
                        {{ ($subscription->trial_ends_at ?? $subscription->renews_at)?->translatedFormat('j \d\e F \d\e Y') ?? '—' }}
                    </dd>
                </div>
            </dl>

            @if ($subscription->trialDaysLeft() !== null)
                <p class="mt-4 text-sm text-primary-600">
                    Te quedan {{ $subscription->trialDaysLeft() }} días de prueba gratis.
                </p>
            @endif

            @php
                $debeRenovar = in_array($subscription->status?->value, ['past_due', 'cancelled'], true)
                    || $subscription->renews_at?->diffInDays(now()) <= 7
                    || $subscription->trialDaysLeft() !== null;
            @endphp

            @if ($debeRenovar && $subscription->plan)
                <div class="mt-6">
                    <x-filament::button
                        tag="a"
                        href="{{ route('subscription.checkout', ['plan' => $subscription->plan->id, 'periodo' => $subscription->billing_period?->value]) }}"
                        icon="heroicon-o-credit-card">
                        {{ $subscription->status?->value === 'past_due' ? 'Activar mi plan' : 'Renovar mi plan' }}
                    </x-filament::button>
                </div>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Qué incluye tu plan</x-slot>

            <ul class="divide-y divide-gray-100 dark:divide-white/5">
                @foreach ($this->features() as $feature)
                    <li class="flex items-start gap-3 py-3">
                        <span @class([
                            'mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-xs font-bold',
                            'bg-success-100 text-success-700' => $feature['included'],
                            'bg-gray-100 text-gray-400 dark:bg-white/5' => ! $feature['included'],
                        ])>{{ $feature['included'] ? '✓' : '—' }}</span>

                        <span>
                            <span @class([
                                'block font-medium',
                                'text-gray-400 line-through' => ! $feature['included'],
                            ])>{{ $feature['title'] }}</span>
                            <span class="block text-sm text-gray-500">{{ $feature['description'] }}</span>
                        </span>
                    </li>
                @endforeach
            </ul>
        </x-filament::section>

        @if ($this->upgrades()->isNotEmpty())
            <x-filament::section>
                <x-slot name="heading">Si necesitas más</x-slot>

                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($this->upgrades() as $plan)
                        <div class="rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10 p-4">
                            <p class="font-semibold">{{ $plan->name }}</p>
                            <p class="text-sm text-gray-500">{{ $plan->tagline }}</p>
                            <p class="mt-2 font-bold text-primary-600">
                                ${{ number_format($plan->price_monthly, 0, ',', '.') }} / mes
                            </p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex flex-wrap gap-3">
                    @foreach ($this->upgrades() as $plan)
                        <x-filament::button
                            tag="a"
                            color="gray"
                            href="{{ route('subscription.checkout', ['plan' => $plan->id, 'periodo' => 'monthly']) }}">
                            Cambiarme a {{ $plan->name }}
                        </x-filament::button>
                    @endforeach
                </div>
            </x-filament::section>
        @endif
    @endif
</x-filament-panels::page>
