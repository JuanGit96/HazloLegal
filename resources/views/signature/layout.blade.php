{{--
    Pantalla de firma: pensada solo para el celular y sin nada alrededor.
    Quien llega aquí ya decidió; cualquier enlace de navegación sería una
    oportunidad de abandonar.
--}}
<!DOCTYPE html>
<html lang="es-CO">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="theme-color" content="#1D4ED8">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Firmar documento') · HazloLegal</title>
    <link rel="stylesheet" href="@asset('css/site.css')">
    <link rel="icon" href="{{ asset('img/favicon.svg') }}" type="image/svg+xml">
    @stack('head')
</head>
<body class="sign-body">
    <main class="sign">
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
