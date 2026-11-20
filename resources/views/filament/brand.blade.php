{{-- El logo de la marca dentro de Filament. El panel tiene tema oscuro, así
     que el marcador va en naranja: el azul se pierde contra el fondo. --}}
<span style="display:inline-flex; align-items:baseline; gap:.12em; font-weight:800; font-size:1.15rem; letter-spacing:-.035em; line-height:1.1;">
    <span>Hazlo</span>
    <span style="display:inline-block; padding:.04em .3em .14em; background:#F97316; color:#fff; border-radius:7px; transform:rotate(-2deg);">Legal</span>
    @isset($sufijo)
        <span style="font-weight:600; opacity:.6; font-size:.8em;">· {{ $sufijo }}</span>
    @endisset
</span>
