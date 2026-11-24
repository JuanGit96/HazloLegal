@php $consent = app(\App\Domain\Shared\Services\CookieConsentService::class); @endphp

@if ($consent->shouldAsk())
    {{-- Dos formularios hermanos, no uno con JavaScript: así «rechazar»
         cuesta un clic igual que «aceptar». Si aceptar fuera más fácil que
         rechazar, la autorización no sería libre y no valdría. --}}
    <aside class="cookie-bar" role="region" aria-label="Aviso de cookies" id="aviso-cookies">
        <div class="cookie-bar__inner">
            <div class="cookie-bar__text">
                <h2 class="cookie-bar__title">Antes de seguir: cookies</h2>
                <p>
                    Las necesarias ya están puestas, porque sin ellas no podrías armar un trámite.
                    Para medir qué páginas se leen y dónde se abandona un trámite necesitamos tu
                    permiso, y puedes quitarlo cuando quieras.
                    <a href="{{ route('pages.cookies') }}">Qué guarda cada una</a>.
                </p>
            </div>

            <div class="cookie-bar__actions">
                <form action="{{ route('cookies.store') }}" method="post">
                    @csrf
                    <input type="hidden" name="decision" value="necessary">
                    <input type="hidden" name="redirect" value="{{ request()->getRequestUri() }}">
                    <button type="submit" class="btn btn--outline btn--sm">Solo las necesarias</button>
                </form>

                <form action="{{ route('cookies.store') }}" method="post">
                    @csrf
                    <input type="hidden" name="decision" value="all">
                    <input type="hidden" name="redirect" value="{{ request()->getRequestUri() }}">
                    <button type="submit" class="btn btn--primary btn--sm">Aceptar todas</button>
                </form>

                <a href="{{ route('pages.cookies') }}" class="cookie-bar__link">Elegir una por una</a>
            </div>
        </div>
    </aside>
@endif
