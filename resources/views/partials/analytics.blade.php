@php
    $consent = app(\App\Domain\Shared\Services\CookieConsentService::class);
    $ga = config('hazlolegal.seo.ga_measurement_id');
    $pixel = config('hazlolegal.seo.meta_pixel_id');
@endphp

{{-- Nada de esto se carga antes de la autorización, y por eso está aquí y no
     en el layout: una etiqueta de medición puesta «mientras el visitante
     decide» ya trató sus datos sin permiso, y el banner sobraría. --}}

@if ($ga && $consent->allows('analytics'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', @json($ga), { anonymize_ip: true });
    </script>
@endif

@if ($pixel && $consent->allows('marketing'))
    <script>
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
        n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
        document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', @json($pixel));
        fbq('track', 'PageView');
    </script>
@endif
