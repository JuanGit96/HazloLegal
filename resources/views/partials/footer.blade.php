@php
    $wa = app(\App\Domain\Shared\Services\WhatsAppLinkBuilder::class);
    $social = config('hazlolegal.social');
    $contact = config('hazlolegal.contact');
    $icons = [
        'facebook' => '<path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5.02 3.66 9.18 8.44 9.94v-7.03H7.9v-2.9h2.54V9.85c0-2.51 1.49-3.9 3.77-3.9 1.09 0 2.23.2 2.23.2v2.46h-1.26c-1.24 0-1.63.78-1.63 1.57v1.88h2.78l-.45 2.9h-2.33V22c4.78-.76 8.45-4.92 8.45-9.94z"/>',
        'instagram' => '<path d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41a3.7 3.7 0 0 1-1.38-.9 3.7 3.7 0 0 1-.9-1.38c-.16-.42-.36-1.06-.41-2.23C2.17 15.58 2.16 15.2 2.16 12s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41C8.42 2.17 8.8 2.16 12 2.16zm0 1.8c-3.14 0-3.5.01-4.73.07-.92.04-1.4.19-1.73.32-.43.17-.74.37-1.06.69-.32.32-.52.63-.69 1.06-.13.33-.28.81-.32 1.73-.06 1.23-.07 1.59-.07 4.73s.01 3.5.07 4.73c.04.92.19 1.4.32 1.73.17.43.37.74.69 1.06.32.32.63.52 1.06.69.33.13.81.28 1.73.32 1.23.06 1.59.07 4.73.07s3.5-.01 4.73-.07c.92-.04 1.4-.19 1.73-.32.43-.17.74-.37 1.06-.69.32-.32.52-.63.69-1.06.13-.33.28-.81.32-1.73.06-1.23.07-1.59.07-4.73s-.01-3.5-.07-4.73c-.04-.92-.19-1.4-.32-1.73a2.9 2.9 0 0 0-.69-1.06 2.9 2.9 0 0 0-1.06-.69c-.33-.13-.81-.28-1.73-.32-1.23-.06-1.59-.07-4.73-.07zm0 3.06a4.98 4.98 0 1 1 0 9.96 4.98 4.98 0 0 1 0-9.96zm0 8.21a3.23 3.23 0 1 0 0-6.46 3.23 3.23 0 0 0 0 6.46zm6.34-8.41a1.16 1.16 0 1 1-2.32 0 1.16 1.16 0 0 1 2.32 0z"/>',
        'linkedin' => '<path d="M6.94 5.5a2.06 2.06 0 1 1-4.12 0 2.06 2.06 0 0 1 4.12 0zM3.15 8.98h3.57V21H3.15V8.98zm5.81 0h3.42v1.64h.05c.48-.9 1.64-1.86 3.38-1.86 3.61 0 4.28 2.38 4.28 5.47V21h-3.57v-5.93c0-1.41-.03-3.23-1.97-3.23-1.97 0-2.27 1.54-2.27 3.13V21H8.96V8.98z"/>',
        'tiktok' => '<path d="M16.6 5.82A4.28 4.28 0 0 1 15.54 3h-3.1v12.4a2.59 2.59 0 1 1-1.85-2.48V9.77a5.72 5.72 0 1 0 4.95 5.66V8.9a7.3 7.3 0 0 0 4.27 1.38V7.17a4.25 4.25 0 0 1-3.21-1.35z"/>',
        'youtube' => '<path d="M21.58 7.19a2.51 2.51 0 0 0-1.77-1.78C18.25 5 12 5 12 5s-6.25 0-7.81.41A2.51 2.51 0 0 0 2.42 7.2 26.2 26.2 0 0 0 2 12a26.2 26.2 0 0 0 .42 4.81 2.51 2.51 0 0 0 1.77 1.78C5.75 19 12 19 12 19s6.25 0 7.81-.41a2.51 2.51 0 0 0 1.77-1.78A26.2 26.2 0 0 0 22 12a26.2 26.2 0 0 0-.42-4.81zM10 15.5v-7l6 3.5-6 3.5z"/>',
    ];
    $labels = ['facebook' => 'Facebook', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn', 'tiktok' => 'TikTok', 'youtube' => 'YouTube'];
@endphp

<footer class="site-footer">
    <div class="container">
        <div class="site-footer__grid">
            <div class="site-footer__about">
                <a href="{{ route('home') }}" class="logo" style="margin-bottom: var(--s4);">
                    <span class="logo__mark" aria-hidden="true">⚖</span>
                    Hazlo<em>Legal</em>
                </a>
                <p>Documentos y trámites legales construidos por abogados colombianos, con precio fijo publicado y garantía de corrección por escrito.</p>

                <div class="socials">
                    @foreach ($icons as $key => $path)
                        @if (!empty($social[$key]))
                            <a href="{{ $social[$key] }}" class="social" target="_blank" rel="noopener me" aria-label="{{ $labels[$key] }} de Hazlo Legal" title="{{ $labels[$key] }}">
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">{!! $path !!}</svg>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>

            <div>
                <h4>Trámites populares</h4>
                <div class="footer-links">
                    @foreach ($footerDocuments ?? app(\App\Domain\Catalog\Services\CatalogService::class)->featuredDocuments(6) as $doc)
                        <a href="{{ $doc->url() }}">{{ $doc->short_name ?: $doc->name }}</a>
                    @endforeach
                    <a href="{{ route('documents.index') }}"><strong>Ver todo el catálogo →</strong></a>
                </div>
            </div>

            <div>
                <h4>La empresa</h4>
                <div class="footer-links">
                    <a href="{{ route('pages.about') }}">Quiénes somos</a>
                    <a href="{{ route('pages.how-it-works') }}">Cómo funciona</a>
                    <a href="{{ route('pages.guarantee') }}">Certificado de Garantía</a>
                    <a href="{{ route('cases.create') }}">Casos complejos</a>
                    <a href="{{ route('blog.index') }}">Guías gratis</a>
                </div>

                <h4 style="margin-top: var(--s8);">Legal</h4>
                <div class="footer-links">
                    <a href="{{ route('pages.terms') }}">Términos y condiciones</a>
                    <a href="{{ route('pages.privacy') }}">Tratamiento de datos</a>
                    <a href="{{ route('pages.signature') }}">Validez de la firma electrónica</a>
                </div>
            </div>

            <div>
                <h4>Contacto</h4>
                <div class="footer-links">
                    <a href="{{ $wa->link('Hola, quiero información sobre un trámite.') }}" target="_blank" rel="noopener">
                        WhatsApp {{ $contact['whatsapp_display'] }}
                    </a>
                    <a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a>
                    <a href="tel:{{ preg_replace('/\s+/', '', $contact['phone']) }}">{{ $contact['phone'] }}</a>
                </div>
                <p class="text-xs text-muted" style="margin-top: var(--s5);">
                    {{ $contact['address'] }}<br>
                    {{ $contact['city'] }}, Colombia<br>
                    <span style="color: var(--success); font-weight: 600;">{{ $contact['hours'] }}</span>
                </p>
            </div>
        </div>

        <div class="site-footer__bottom">
            <p>
                &copy; {{ now()->year }} {{ config('hazlolegal.brand.legal_name') }} · NIT {{ config('hazlolegal.brand.nit') }}.
                El uso de este sitio no constituye una relación abogado&#8209;cliente hasta la aceptación formal y el pago del servicio.
                La información publicada es orientativa y no reemplaza una asesoría particular.
            </p>
            <div class="site-footer__legal">
                <a href="{{ route('pages.terms') }}">Términos</a>
                <a href="{{ route('pages.privacy') }}">Privacidad</a>
                <a href="{{ route('sitemap') }}">Sitemap</a>
            </div>
        </div>
    </div>
</footer>
