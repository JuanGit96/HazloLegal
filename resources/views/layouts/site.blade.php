<!DOCTYPE html>
<html lang="es-CO" prefix="og: https://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1E3A8A">

    @include('partials.seo', ['meta' => $meta ?? null])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">

    <link rel="icon" href="{{ asset('img/favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('img/favicon.svg') }}">

    @stack('head')
</head>
<body>
    <a class="skip-link" href="#contenido">Saltar al contenido</a>

    @include('partials.header')

    <main id="contenido">
        @if (session('status'))
            <div class="container" style="margin-top: var(--s6);">
                <div class="alert alert--success" role="status">
                    <span class="alert__icon" aria-hidden="true">✓</span>
                    <div>{{ session('status') }}</div>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    @include('partials.footer')
    @include('partials.whatsapp-float')

    <script src="{{ asset('js/site.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
