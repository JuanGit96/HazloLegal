@props(['items' => []])

@if (count($items) > 1)
    <nav class="breadcrumbs" aria-label="Ruta de navegación">
        @foreach ($items as $i => $item)
            @if ($i > 0)<span aria-hidden="true">/</span>@endif
            @if ($loop->last || empty($item['url']))
                <span aria-current="page">{{ $item['name'] }}</span>
            @else
                <a href="{{ $item['url'] }}">{{ $item['name'] }}</a>
            @endif
        @endforeach
    </nav>
@endif
