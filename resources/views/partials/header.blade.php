<header class="site-header" data-nav>
    <div class="container site-header__inner">
        <a href="{{ route('home') }}" class="logo" aria-label="HazloLegal, inicio">
            <span class="logo__word">Hazlo</span><span class="logo__hl">Legal</span>
        </a>

        <nav class="nav" aria-label="Navegación principal">
            {{-- «Gratis» va primero y marcado: es la puerta de entrada del
                 embudo, y el único enlace del menú que promete algo. --}}
            <a href="{{ route('tools.index') }}" class="nav__free" @if(request()->routeIs('tools.*')) aria-current="page" @endif>
                Gratis
            </a>
            <a href="{{ route('documents.index') }}" @if(request()->routeIs('documents.*')) aria-current="page" @endif>Trámites</a>
            <a href="{{ route('cases.create') }}" @if(request()->routeIs('cases.*')) aria-current="page" @endif>Caso complejo</a>
            <a href="{{ route('lawyers.index') }}" @if(request()->routeIs('lawyers.index') || request()->routeIs('lawyers.show')) aria-current="page" @endif>Abogados</a>
            <a href="{{ route('pages.how-it-works') }}" @if(request()->routeIs('pages.how-it-works')) aria-current="page" @endif>Cómo funciona</a>
            <a href="{{ route('blog.index') }}" @if(request()->routeIs('blog.*')) aria-current="page" @endif>Blog</a>

            {{-- Otros públicos: quien ya compró y quien viene a vender. Se
                 separan en vez de competir con los enlaces del cliente nuevo. --}}
            <a href="{{ route('account.request') }}" class="nav__pro" @if(request()->routeIs('account.*')) aria-current="page" @endif>
                Soy cliente
            </a>
            <a href="{{ route('lawyers.join') }}" class="nav__pro nav__pro--plain" @if(request()->routeIs('lawyers.join')) aria-current="page" @endif>
                Soy abogado
            </a>
        </nav>

        <div class="header-actions">
            <a href="{{ route('account.request') }}" class="btn btn--ghost btn--sm nav__pro-btn">Soy cliente</a>
            <a href="{{ route('lawyers.join') }}" class="btn btn--ghost btn--sm nav__pro-btn">Soy abogado</a>
            <a href="{{ route('documents.index') }}" class="btn btn--primary btn--sm">Buscar mi trámite</a>
            <button type="button" class="nav-toggle" data-nav-toggle aria-expanded="false" aria-label="Abrir menú">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="M3 5h14M3 10h14M3 15h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
    </div>
</header>
