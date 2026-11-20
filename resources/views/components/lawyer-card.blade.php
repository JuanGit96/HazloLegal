@props(['lawyer', 'href' => null])

@php
    $link = $href ?? ($lawyer->is_published ? route('lawyers.show', $lawyer->slug) : null);
@endphp

<{{ $link ? 'a' : 'div' }} @if ($link) href="{{ $link }}" @endif class="lawyer-card">
    <div class="lawyer-card__avatar" aria-hidden="true">{{ $lawyer->initials() }}</div>
    <h3>{{ $lawyer->name }}</h3>
    <p class="lawyer-card__role">
        {{ $lawyer->headline ?: ($lawyer->isFirm() ? 'Firma de abogados' : 'Abogado titulado') }}
    </p>

    @if ($lawyer->isFirm())
        <p class="lawyer-card__cred">
            {{ $lawyer->team_size ? $lawyer->team_size.' abogados · ' : '' }}{{ $lawyer->city }}
        </p>
    @elseif ($lawyer->years_experience)
        <p class="lawyer-card__cred">{{ $lawyer->years_experience }} años de ejercicio · {{ $lawyer->city }}</p>
    @endif

    @if ($lawyer->bio)
        <p class="lawyer-card__bio">{{ \Illuminate\Support\Str::limit($lawyer->bio, 130) }}</p>
    @endif

    <div class="lawyer-card__tags">
        @foreach ($lawyer->practiceAreaEnums() as $area)
            <span class="pill pill--outline">{{ $area->emoji() }} {{ $area->getLabel() }}</span>
        @endforeach
    </div>

    @if ($link)
        <span class="lawyer-card__more">Ver perfil →</span>
    @endif
</{{ $link ? 'a' : 'div' }}>
