@props(['size' => null])

<div class="seal" @if ($size) style="width: {{ $size }}px; height: {{ $size }}px;" @endif role="img"
     aria-label="Certificado de Garantía Hazlo Legal, versión {{ config('hazlolegal.guarantee.version') }}">
    <div>
        <div class="seal__icon" aria-hidden="true">🛡️</div>
        <div class="seal__text">Certificado de Garantía</div>
        <div class="seal__version">v{{ config('hazlolegal.guarantee.version') }} · Hazlo Legal</div>
    </div>
</div>
