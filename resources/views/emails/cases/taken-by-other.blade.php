@extends('emails.layout')

@section('subject', "El caso {$case->reference} ya fue tomado")
@section('accent', '#94A3B8')
@section('preheader', 'Otro colega aceptó este caso. Te avisamos del siguiente.')
@section('footnote', 'Te avisamos para que no pierdas tiempo revisando un caso que ya tiene abogado.')

@section('content')
    <h1 class="hl-h1" style="margin:0 0 12px 0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:24px; line-height:32px; font-weight:800; letter-spacing:-.4px; color:#0F172A;">
        Este caso ya tiene abogado
    </h1>

    <p style="margin:0 0 22px 0;">
        Hola {{ \Illuminate\Support\Str::before($lawyer->name, ' ') }}, el caso
        <strong>{{ $case->reference }}</strong> ({{ $case->title }}) fue aceptado por otro
        colega de la red. Te escribimos para que no inviertas tiempo en revisarlo.
    </p>

    <x-email.note tone="muted" icon="🔔">
        Esto no afecta tu prioridad. Te seguiremos enviando los casos de
        {{ \Illuminate\Support\Str::lower($case->practice_area->getLabel()) }} que entren.
    </x-email.note>

    <p style="margin:0; font-size:14px; line-height:22px; color:#64748B;">
        Si quieres recibir más o menos invitaciones, o ajustar tus áreas de práctica,
        respóndenos este correo y lo configuramos.
    </p>
@endsection
