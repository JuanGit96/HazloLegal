@extends('emails.layout')

@section('subject', "{$message->kind->getLabel()} · {$message->reference}")
@section('accent', $message->kind->value === 'data_deletion' ? '#DC2626' : '#2563EB')
@section('preheader', "{$message->name} escribió desde el sitio.")

@section('content')
    <h1 class="hl-h1" style="margin:0 0 12px 0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:24px; line-height:31px; font-weight:800; color:#0F172A;">
        {{ $message->kind->getLabel() }}
    </h1>

    @if ($message->kind->value === 'data_deletion')
        <x-email.note tone="warn" icon="⏳">
            <strong>Plazo legal: {{ $message->kind->responseDays() }} días hábiles</strong> (Ley 1581 de 2012).
            Vence el {{ $message->dueAt()?->translatedFormat('l j \d\e F') }}.
        </x-email.note>
    @endif

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 26px 0;">
        <x-email.row label="Radicado" :value="$message->reference" />
        <x-email.row label="Nombre" :value="$message->name" />
        <x-email.row label="Correo" :value="$message->email" />
        @if ($message->phone)
            <x-email.row label="Teléfono" :value="$message->phone" />
        @endif
        @if ($message->document_number)
            <x-email.row label="Documento" :value="trim($message->document_type.' '.$message->document_number)" />
        @endif
        @if ($message->subject)
            <x-email.row label="Asunto" :value="$message->subject" />
        @endif
    </table>

    <p style="margin:0 0 8px 0; font-weight:700; color:#0F172A;">Lo que escribió:</p>
    <p style="margin:0 0 26px 0; padding:16px 18px; background-color:#F8FAFC; border-left:4px solid #CBD5E1; white-space:pre-line;">{{ $message->message }}</p>

    <p style="margin:0; font-size:14px; line-height:22px; color:#64748B;">
        Puedes responder directamente a este correo: la respuesta le llega a {{ $message->email }}.
    </p>
@endsection
