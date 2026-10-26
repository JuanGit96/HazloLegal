@extends('layouts.site')

@section('content')
@php
    use App\Domain\LegalCases\Enums\AssignmentOutcome;

    $outcome = $result->outcome;

    $display = match ($outcome) {
        AssignmentOutcome::Won => [
            'icon' => '🎉', 'tone' => 'success',
            'title' => '¡El caso es tuyo!',
            'lead' => 'Llegaste primero. El cliente ya recibió un correo con tu nombre y tus credenciales.',
        ],
        AssignmentOutcome::AlreadyYours => [
            'icon' => '✓', 'tone' => 'success',
            'title' => 'Este caso ya estaba asignado a ti',
            'lead' => 'No hace falta volver a aceptarlo. Abajo tienes los datos de contacto del cliente.',
        ],
        AssignmentOutcome::TakenByOther => [
            'icon' => '⏱️', 'tone' => 'warn',
            'title' => 'Otro abogado llegó primero',
            'lead' => 'Este caso ya fue aceptado por un colega de la red. Te avisaremos del siguiente.',
        ],
        AssignmentOutcome::Declined => [
            'icon' => '👍', 'tone' => 'info',
            'title' => 'Registramos que no tomas este caso',
            'lead' => 'Gracias por responder rápido: así el caso llega antes a otro colega. No afecta tu prioridad en los próximos.',
        ],
        AssignmentOutcome::Expired => [
            'icon' => '🕒', 'tone' => 'warn',
            'title' => 'La invitación ya venció',
            'lead' => 'Pasó el plazo para responder a este caso. El equipo lo va a reasignar.',
        ],
        AssignmentOutcome::Unavailable => [
            'icon' => '⚠️', 'tone' => 'warn',
            'title' => 'Este caso ya no está disponible',
            'lead' => 'Fue cerrado o anulado por el equipo de HazloLegal.',
        ],
    };
@endphp

<section class="section section--white">
    <div class="container container--narrow">
        <div class="center">
            <div style="display: grid; place-items: center; width: 84px; height: 84px; margin: 0 auto var(--s6);
                        background: var(--slate-100); border-radius: 50%; font-size: 2.4rem;" aria-hidden="true">
                {{ $display['icon'] }}
            </div>

            <h1>{{ $display['title'] }}</h1>
            <p class="text-muted" style="font-size: var(--t-lg);">{{ $display['lead'] }}</p>

            <p class="pill pill--brand" style="font-size: var(--t-base); padding: var(--s3) var(--s5);">
                Caso <strong class="mono">{{ $case->reference }}</strong>
            </p>
        </div>

        {{-- Datos del cliente: sólo para quien ganó el caso. --}}
        @if ($outcome->isSuccess())
            <div class="card card--pad-lg mt-10" style="border-color: var(--green-600); background: var(--green-50);">
                <h2 style="font-size: var(--t-lg);">Datos de contacto del cliente</h2>
                <p class="text-sm text-muted">Contáctalo dentro de las próximas 24 horas. Es el compromiso de la red.</p>

                <dl class="summary__rows" style="margin-top: var(--s5); margin-bottom: 0;">
                    <div class="summary__row"><dt>Nombre</dt><dd>{{ $case->client_name }}</dd></div>
                    <div class="summary__row">
                        <dt>Correo</dt>
                        <dd><a href="mailto:{{ $case->client_email }}">{{ $case->client_email }}</a></dd>
                    </div>
                    <div class="summary__row">
                        <dt>Celular</dt>
                        <dd>
                            <a href="https://wa.me/{{ preg_replace('/\D+/', '', $case->client_phone) }}" target="_blank" rel="noopener">
                                {{ $case->client_phone }}
                            </a>
                        </dd>
                    </div>
                    @if ($case->client_city)
                        <div class="summary__row"><dt>Ciudad</dt><dd>{{ $case->client_city }}</dd></div>
                    @endif
                </dl>
            </div>

            <div class="card card--pad-lg mt-6">
                <h2 style="font-size: var(--t-lg);">El caso</h2>

                <dl class="summary__rows" style="margin-bottom: var(--s5);">
                    <div class="summary__row"><dt>Área</dt><dd>{{ $case->practice_area->getLabel() }}</dd></div>
                    <div class="summary__row"><dt>Urgencia</dt><dd>{{ $case->urgency->getLabel() }}</dd></div>
                    @if ($case->budget_range)
                        <div class="summary__row"><dt>Presupuesto indicado</dt><dd>{{ $case->budget_range->getLabel() }}</dd></div>
                    @endif
                    @if ($case->deadline_note)
                        <div class="summary__row"><dt>Término</dt><dd>{{ $case->deadline_note }}</dd></div>
                    @endif
                    <div class="summary__row">
                        <dt>Documentos</dt>
                        <dd>{{ $case->has_documents ? 'El cliente dice que sí los tiene' : 'No indicó' }}</dd>
                    </div>
                </dl>

                <h3 style="font-size: var(--t-base);">{{ $case->title }}</h3>
                <p class="text-sm" style="white-space: pre-line;">{{ $case->description }}</p>
            </div>

            <div class="alert alert--info mt-6">
                <span class="alert__icon" aria-hidden="true">🔒</span>
                <div>
                    <strong>Reserva profesional.</strong>
                    <p style="margin-top: var(--s1);">
                        La información de este caso es confidencial. No la compartas fuera de la red
                        ni la uses para fines distintos de la atención del cliente.
                    </p>
                </div>
            </div>

        {{-- Formulario de motivo: útil para mejorar el reparto. --}}
        @elseif ($outcome === AssignmentOutcome::Declined && $offer->decline_reason === null)
            <div class="card card--pad-lg mt-10">
                <h2 style="font-size: var(--t-lg);">¿Nos cuentas por qué? (opcional)</h2>
                <p class="text-sm text-muted">
                    Nos sirve para afinar a quién le enviamos cada caso y no hacerte perder tiempo.
                </p>

                <form action="{{ route('cases.offer.decline', $offer->token) }}" method="post" class="form" style="margin-top: var(--s5);">
                    @csrf
                    <div class="field">
                        <label for="decline_reason" class="visually-hidden">Motivo</label>
                        <textarea id="decline_reason" name="decline_reason" class="textarea" maxlength="500"
                                  style="min-height: 110px;"
                                  placeholder="Ejemplo: no es mi especialidad / no tengo cupo esta semana / el término ya venció"></textarea>
                    </div>
                    <button type="submit" class="btn btn--outline">Enviar motivo</button>
                </form>
            </div>

        @elseif ($outcome === AssignmentOutcome::TakenByOther && $case->assignedLawyer)
            <div class="card card--flat mt-10" style="background: var(--slate-50);">
                <p class="text-sm text-muted mb-0">
                    Tomado por <strong>{{ $case->assignedLawyer->name }}</strong>
                    @if ($case->assigned_at)
                        · {{ $case->assigned_at->diffForHumans() }}
                    @endif
                </p>
            </div>
        @endif

        <div class="btn-row btn-row--center mt-10">
            <a href="{{ route('home') }}" class="btn btn--outline">Ir a HazloLegal</a>
            <a href="mailto:{{ config('hazlolegal.contact.cases_email') }}?subject=Caso%20{{ $case->reference }}" class="btn btn--ghost">
                Escribir al equipo
            </a>
        </div>
    </div>
</section>
@endsection
