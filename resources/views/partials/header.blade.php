<header class="site-header" data-nav>
    <div class="container site-header__inner">
        <a href="{{ route('home') }}" class="logo" aria-label="Hazlo Legal, inicio">
            <span class="logo__mark" aria-hidden="true">⚖</span>
            Hazlo<em>Legal</em>
        </a>

        <nav class="nav" aria-label="Navegación principal">
            <a href="{{ route('documents.index') }}" @if(request()->routeIs('documents.*')) aria-current="page" @endif>Trámites</a>
            <a href="{{ route('pages.how-it-works') }}" @if(request()->routeIs('pages.how-it-works')) aria-current="page" @endif>Cómo funciona</a>
            <a href="{{ route('pages.guarantee') }}" @if(request()->routeIs('pages.guarantee')) aria-current="page" @endif>Garantía</a>
            <a href="{{ route('cases.create') }}" @if(request()->routeIs('cases.create')) aria-current="page" @endif>Caso complejo</a>
            <a href="{{ route('blog.index') }}" @if(request()->routeIs('blog.*')) aria-current="page" @endif>Blog</a>
        </nav>

        <div class="header-actions">
            <a href="{{ route('pages.about') }}" class="btn btn--ghost btn--sm">Nosotros</a>
            <a href="{{ route('documents.index') }}" class="btn btn--primary btn--sm">Buscar mi trámite</a>
            <button type="button" class="nav-toggle" data-nav-toggle aria-expanded="false" aria-label="Abrir menú">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="M3 5h14M3 10h14M3 15h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
    </div>
</header>
