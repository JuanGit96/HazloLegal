@props([
    'eyebrow' => null,
    'title',
    'text' => null,
    'primaryLabel' => 'Elegir mi trámite',
    'primaryHref' => null,
    'secondaryLabel' => null,
    'secondaryHref' => null,
    'note' => null,
])

<section class="section">
    <div class="container">
        <div class="cta-band reveal">
            @if ($eyebrow)<span class="eyebrow">{{ $eyebrow }}</span>@endif
            <h2>{{ $title }}</h2>
            @if ($text)<p>{{ $text }}</p>@endif

            <div class="btn-row btn-row--center">
                <a href="{{ $primaryHref ?? route('documents.index') }}" class="btn btn--primary btn--lg">{{ $primaryLabel }}</a>
                @if ($secondaryLabel && $secondaryHref)
                    <a href="{{ $secondaryHref }}" class="btn btn--white btn--lg">{{ $secondaryLabel }}</a>
                @endif
            </div>

            @if ($note)<p class="cta-band__note">{{ $note }}</p>@endif
        </div>
    </div>
</section>
