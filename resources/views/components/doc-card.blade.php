@props(['document', 'price' => null, 'href' => null])

<a href="{{ $href ?? $document->url() }}"
   @class(['doc-card', 'doc-card--manual' => ! $document->isAutomated()])>
    <span class="doc-card__icon" aria-hidden="true">{{ $document->emojiOrArea() }}</span>

    <span class="doc-card__body">
        <h3>{{ $document->name }}</h3>
        <p>{{ $document->tagline ?: \Illuminate\Support\Str::limit($document->summary, 110) }}</p>

        <span class="doc-card__meta">
            @if ($price !== null)
                <span class="doc-card__price">{{ $price }}</span>
            @endif
            @if ($document->isAutomated())
                <span class="pill pill--success">Entrega 24 h</span>
            @else
                <span class="pill pill--warn">Redacción a medida</span>
            @endif
            @if ($document->supports_signature)
                <span class="pill pill--brand">Firma digital</span>
            @endif
        </span>
    </span>

    <span class="doc-card__arrow" aria-hidden="true">
        <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M6.5 3.5L12 9l-5.5 5.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </span>
</a>
