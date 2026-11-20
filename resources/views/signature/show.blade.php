@extends('signature.layout')

@section('title', 'Firmar '.$order->document->name)

@section('content')
<div class="sign__head">
    <span class="logo" style="justify-content: center;">
        <span class="logo__word">Hazlo</span><span class="logo__hl">Legal</span>
    </span>
    <h1>Firma <span class="hl hl--blue">tu documento</span></h1>
    <p>{{ $order->document->name }} · {{ $order->reference }}</p>
</div>

@if ($errors->any())
    <div class="alert alert--danger">
        <span class="alert__icon" aria-hidden="true">!</span>
        <div>
            @foreach ($errors->all() as $error)<p class="mb-0">{{ $error }}</p>@endforeach
        </div>
    </div>
@endif

<form action="{{ route('signature.store', $signature->token) }}" method="post" id="sign-form">
    @csrf
    <input type="hidden" name="signature" id="signature-data">

    <div class="sign__who">
        <span>Firma como</span>
        <strong>{{ $signature->signer_name }}</strong>
        @if ($signature->signer_document)
            <small>{{ $signature->signer_document }}</small>
        @endif
    </div>

    <p class="sign__hint">Traza tu firma con el dedo dentro del recuadro</p>

    <div class="sign__pad">
        <canvas id="pad" aria-label="Área para firmar"></canvas>
        <span class="sign__line" aria-hidden="true"></span>
        <button type="button" class="sign__clear" id="clear">Borrar</button>
    </div>

    <label class="checkbox sign__check">
        <input type="checkbox" name="confirms" value="1" required>
        <span>
            Confirmo que soy <strong>{{ $signature->signer_name }}</strong> y que esta es mi firma.
            Entiendo que tiene validez legal según la Ley 527 de 1999.
        </span>
    </label>

    <button type="submit" class="btn btn--primary btn--block btn--lg" id="submit" disabled>
        Firmar documento
    </button>

    <p class="sign__legal">
        Guardamos la fecha, la hora, tu dirección IP y el dispositivo desde el que firmas.
        Es lo que permite demostrar después que fuiste tú.
    </p>
</form>
@endsection

@push('scripts')
<script>
/* Firma a dedo sobre canvas. Sin librerías: son 60 líneas y una dependencia
   más es una cosa más que puede romperse en un celular viejo. */
(function () {
    var canvas = document.getElementById('pad');
    var form = document.getElementById('sign-form');
    var field = document.getElementById('signature-data');
    var submit = document.getElementById('submit');
    var clear = document.getElementById('clear');
    if (!canvas) return;

    var ctx = canvas.getContext('2d');
    var drawing = false;
    var dirty = false;
    var paths = [];
    var current = null;

    function resize() {
        var ratio = window.devicePixelRatio || 1;
        var rect = canvas.getBoundingClientRect();
        canvas.width = rect.width * ratio;
        canvas.height = rect.height * ratio;
        ctx.scale(ratio, ratio);
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.strokeStyle = '#10151F';
    }

    function point(e) {
        var rect = canvas.getBoundingClientRect();
        var t = e.touches ? e.touches[0] : e;
        return { x: t.clientX - rect.left, y: t.clientY - rect.top };
    }

    function start(e) {
        e.preventDefault();
        drawing = true;
        current = [point(e)];
        paths.push(current);
    }

    function move(e) {
        if (!drawing) return;
        e.preventDefault();
        var p = point(e);
        current.push(p);
        dirty = true;

        ctx.beginPath();
        ctx.moveTo(current[current.length - 2].x, current[current.length - 2].y);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();

        submit.disabled = false;
    }

    function end() { drawing = false; }

    ['mousedown', 'touchstart'].forEach(function (ev) { canvas.addEventListener(ev, start, { passive: false }); });
    ['mousemove', 'touchmove'].forEach(function (ev) { canvas.addEventListener(ev, move, { passive: false }); });
    ['mouseup', 'mouseleave', 'touchend', 'touchcancel'].forEach(function (ev) { canvas.addEventListener(ev, end); });

    clear.addEventListener('click', function () {
        paths = [];
        dirty = false;
        submit.disabled = true;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    });

    /* Se manda SVG y no PNG: pesa menos, se imprime nítido a cualquier
       tamaño y se puede volver a dibujar sobre el PDF sin perder calidad. */
    form.addEventListener('submit', function (e) {
        if (!dirty) { e.preventDefault(); return; }

        var rect = canvas.getBoundingClientRect();
        var d = paths.map(function (path) {
            return 'M' + path.map(function (p) {
                return p.x.toFixed(1) + ' ' + p.y.toFixed(1);
            }).join(' L');
        }).join(' ');

        field.value = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' +
            Math.round(rect.width) + ' ' + Math.round(rect.height) + '">' +
            '<path d="' + d + '" fill="none" stroke="#10151F" stroke-width="2.5" ' +
            'stroke-linecap="round" stroke-linejoin="round"/></svg>';
    });

    resize();
    window.addEventListener('resize', resize);
})();
</script>
@endpush
