@extends('layouts.site')

@section('content')
<section class="section section--white">
    <div class="container">
        <div class="section-head">
            <h1>¿Qué nivel de acompañamiento necesitas?</h1>
            <p>Puedes cambiar de opción en cualquier momento antes de pagar.</p>
        </div>

        <div class="pricing-grid">
            @foreach ($plans as $plan)
                @php
                    $href = $plan->is_quote_only
                        ? route('cases.create')
                        : route('checkout.documents', $plan->key->value);
                @endphp
                <div @class(['plan', 'plan--featured' => $plan->is_highlighted, 'plan--dark' => $plan->is_quote_only])>
                    @if ($plan->badge)<span class="badge-float">{{ $plan->badge }}</span>@endif

                    <div class="plan__head">
                        <h2 style="font-size: var(--t-lg); margin-bottom: var(--s2);">{{ $plan->name }}</h2>
                        <p class="plan__tagline">{{ $plan->tagline }}</p>
                    </div>

                    <div class="plan__price">
                        @if ($plan->price === null)
                            Cotizar
                        @else
                            {{ $pricing->formatCop($plan->price) }} <small>COP</small>
                        @endif
                    </div>

                    <ul class="plan__features">
                        @foreach ($plan->includes ?? [] as $feature)
                            <li>{!! \Illuminate\Support\Str::inlineMarkdown($feature) !!}</li>
                        @endforeach
                    </ul>

                    <a href="{{ $href }}"
                       @class(['btn', 'btn--block',
                               'btn--primary' => $plan->is_highlighted,
                               'btn--white' => $plan->is_quote_only,
                               'btn--outline' => ! $plan->is_highlighted && ! $plan->is_quote_only])>
                        {{ $plan->cta_label }}
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
