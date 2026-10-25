@props(['lawyer'])

<div class="lawyer-card">
    <div class="lawyer-card__avatar" aria-hidden="true">{{ $lawyer->initials() }}</div>
    <h3>{{ $lawyer->name }}</h3>
    <p class="lawyer-card__role">{{ $lawyer->headline ?: 'Abogado titulado' }}</p>

    @if ($lawyer->years_experience)
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
</div>
