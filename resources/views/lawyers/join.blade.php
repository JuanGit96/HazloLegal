@extends('layouts.site')

@section('content')
@php $cop = fn ($n) => '$'.number_format((float) $n, 0, ',', '.'); @endphp

{{-- ============================ HERO ============================ --}}
<section class="section section--dark" style="padding-block: var(--s16);">
    <div class="container">
        <div class="hero__grid">
            <div>
                <span class="pill pill--dark">Para abogados y firmas</span>
                <h1 style="margin-top: var(--s5);">
                    Tus próximos clientes ya están aquí, <span class="hl">escribiendo su problema</span>
                </h1>
                <p style="font-size: var(--t-lg); color: var(--blue-100); margin-top: var(--s5);">
                    Cada mes miles de personas generan un derecho de petición o calculan su liquidación
                    en HazloLegal. Muchas descubren a mitad de camino que necesitan un abogado.
                    Nosotros te ponemos ahí, en ese momento exacto.
                </p>

                <div class="btn-row mt-8">
                    <a href="#planes" class="btn btn--primary btn--lg">Ver planes · 2 meses gratis</a>
                    <a href="#registro" class="btn btn--white btn--lg">Quiero mi perfil</a>
                </div>

                <p class="btn-note" style="color: var(--blue-200);">
                    Sin permanencia. Sin comisión sobre tus honorarios. Nunca.
                </p>
            </div>

            <div>
                <div class="stat-row" style="background: rgba(255,255,255,.06); border-radius: var(--r-lg); padding: var(--s8);">
                    <div class="stat">
                        <span class="stat__value" style="color: #fff;">{{ number_format($stats['documents'], 0, ',', '.') }}+</span>
                        <span class="stat__label" style="color: var(--blue-200);">documentos generados</span>
                    </div>
                    <div class="stat">
                        <span class="stat__value" style="color: var(--amber-500);">{{ $stats['areas'] }}</span>
                        <span class="stat__label" style="color: var(--blue-200);">áreas de práctica</span>
                    </div>
                    <div class="stat">
                        <span class="stat__value" style="color: #fff;">0 %</span>
                        <span class="stat__label" style="color: var(--blue-200);">comisión sobre honorarios</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================ QUÉ RECIBES ============================ --}}
<section class="section section--white">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Qué incluye</span>
            <h2>No es un directorio más</h2>
            <p>Un directorio te lista. Nosotros te ponemos delante de alguien que acaba de contarnos su problema por escrito.</p>
        </div>

        <div class="grid grid--3 reveal">
            <div class="tile">
                <span class="tile__arrow" aria-hidden="true">↗</span>
                <div class="tile__icon" aria-hidden="true">🪪</div>
                <h3>Tu página de perfil</h3>
                <p>Una página propia, indexable en Google, con tus áreas, tu experiencia y tu contacto directo. Tuya, no un renglón en una lista.</p>
            </div>

            <div class="tile">
                <span class="tile__arrow" aria-hidden="true">↗</span>
                <div class="tile__icon" aria-hidden="true">📄</div>
                <h3>Tu firma en los instructivos</h3>
                <p>Cada documento que generamos sale con un instructivo PDF. Ese PDF se imprime, se reenvía y se radica en ventanilla. Tu nombre va dentro.</p>
            </div>

            <div class="tile">
                <span class="tile__arrow" aria-hidden="true">↗</span>
                <div class="tile__icon" aria-hidden="true">🔔</div>
                <h3>Casos de tu área, al correo</h3>
                <p>Cuando entra un caso complejo de tu especialidad te avisamos. El primero que lo acepta se lo queda.</p>
            </div>

            <div class="tile">
                <span class="tile__arrow" aria-hidden="true">↗</span>
                <div class="tile__icon" aria-hidden="true">🎯</div>
                <h3>Leads que autorizaron contacto</h3>
                <p>Personas que describieron su caso y marcaron la casilla de «quiero que un abogado me contacte». Con su consentimiento expreso y revocable.</p>
            </div>

            <div class="tile">
                <span class="tile__arrow" aria-hidden="true">↗</span>
                <div class="tile__icon" aria-hidden="true">✍️</div>
                <h3>Nuestro blog te lleva tráfico</h3>
                <p>Nuestros artículos posicionan en Google y enlazan al directorio por especialidad. El contenido trabaja para ti sin que escribas una línea.</p>
            </div>

            <div class="tile">
                <span class="tile__arrow" aria-hidden="true">↗</span>
                <div class="tile__icon" aria-hidden="true">📊</div>
                <h3>Panel con tus números</h3>
                <p>Cuántos vieron tu perfil, cuántos te escribieron, de qué ciudad y qué buscaban. Para saber si la suscripción te está devolviendo lo que cuesta.</p>
            </div>
        </div>
    </div>
</section>

{{-- ============================ PLANES ============================ --}}
<section class="section section--sunken" id="planes">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Planes</span>
            <h2>Dos meses gratis para probarlo</h2>
            <p>Sin tarjeta para empezar. Si no te trae clientes, no sigues. Así de simple.</p>
        </div>

        <div class="pricing-grid reveal">
            @foreach ($plans as $plan)
                <div @class(['plan', 'plan--featured' => $plan->is_highlighted])>
                    @if ($plan->badge)<span class="badge-float">{{ $plan->badge }}</span>@endif

                    <div class="plan__head">
                        <h3>{{ $plan->name }}</h3>
                        <p class="plan__tagline">{{ $plan->tagline }}</p>
                    </div>

                    <div class="plan__price">
                        {{ $cop($plan->price_monthly) }}
                        <small>/ mes</small>
                    </div>

                    <p class="plan__anchor">
                        @if ($plan->free_trial_months > 0)
                            <strong style="color: var(--success);">
                                {{ $plan->free_trial_months }} {{ \Illuminate\Support\Str::plural('mes', $plan->free_trial_months) }} gratis
                            </strong> ·
                        @endif
                        @if ($plan->price_annual)
                            o {{ $cop($plan->price_annual) }} al año
                            <span style="color: var(--success);">(ahorras {{ $cop($plan->annualSaving()) }})</span>
                        @endif
                    </p>

                    <ul class="plan__features">
                        @foreach ($plan->features ?? [] as $feature)<li>{{ $feature }}</li>@endforeach
                    </ul>

                    <a href="#registro" @class(['btn', 'btn--block', 'mt-6', 'btn--primary' => $plan->is_highlighted, 'btn--outline' => ! $plan->is_highlighted])>
                        Empezar con {{ $plan->name }}
                    </a>

                    <p class="plan__foot">Sin permanencia · cancelas cuando quieras</p>
                </div>
            @endforeach
        </div>

        <p class="center mt-8 text-sm text-muted">
            <strong>Nunca cobramos comisión sobre tus honorarios.</strong>
            Lo que acuerdes con tu cliente es tuyo íntegro.
        </p>
    </div>
</section>

{{-- ============================ REGISTRO ============================ --}}
<section class="section section--white" id="registro">
    <div class="container container--narrow">
        <div class="section-head">
            <span class="eyebrow">Solicitud</span>
            <h2>Pide tu perfil</h2>
            <p>Verificamos tu tarjeta profesional antes de publicarte. Es lo que hace que el directorio valga algo.</p>
        </div>

        @if (session('status'))
            <div class="alert alert--success" style="margin-bottom: var(--s8);">
                <span class="alert__icon" aria-hidden="true">✓</span>
                <div>{{ session('status') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert--danger" style="margin-bottom: var(--s8);">
                <span class="alert__icon" aria-hidden="true">!</span>
                <div><ul style="margin:0; padding-left: var(--s5);">
                    @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul></div>
            </div>
        @endif

        <form action="{{ route('lawyers.join.store') }}" method="post" class="form">
            @csrf

            {{-- Lo primero: con quién estamos hablando. Lo que se pide
                 después cambia, y preguntarle el NIT a alguien que ejerce
                 solo es una forma rápida de perderlo. --}}
            <fieldset class="field" style="border: 0; padding: 0; margin: 0;">
                <legend class="field__legend">¿Quién se registra? <span class="req">*</span></legend>

                <div class="choice-grid choice-grid--2" data-kind-switch>
                    @foreach ($kinds as $kind)
                        <label class="choice">
                            <input type="radio" name="kind" value="{{ $kind->value }}" required
                                   @checked(old('kind', 'individual') === $kind->value)>
                            <span class="choice__box">
                                <span class="choice__head">
                                    <span class="choice__title">{{ $kind->getLabel() }}</span>
                                    <span class="choice__radio" aria-hidden="true"></span>
                                </span>
                                <span class="choice__meta">
                                    {{ $kind->value === 'firm'
                                        ? 'Varios abogados bajo un mismo nombre'
                                        : 'Ejerzo por mi cuenta' }}
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <div class="form-row">
                <div class="field">
                    <label for="name">
                        <span data-label-individual>Nombre completo</span>
                        <span data-label-firm hidden>Nombre de la firma</span>
                        <span class="req">*</span>
                    </label>
                    <input type="text" id="name" name="name" class="input" required value="{{ old('name') }}">
                </div>
                <div class="field" data-only-individual>
                    <label for="firm_name">Nombre de la firma</label>
                    <input type="text" id="firm_name" name="firm_name" class="input" value="{{ old('firm_name') }}"
                           placeholder="Si trabajas independiente, déjalo vacío">
                </div>
                <div class="field" data-only-firm hidden>
                    <label for="contact_name">Con quién hablamos <span class="req">*</span></label>
                    <input type="text" id="contact_name" name="contact_name" class="input"
                           value="{{ old('contact_name') }}" placeholder="Nombre del representante">
                    <x-field-error name="contact_name" />
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label for="email">Correo <span class="req">*</span></label>
                    <input type="email" id="email" name="email" class="input" required value="{{ old('email') }}">
                </div>
                <div class="field">
                    <label for="phone">Celular (WhatsApp) <span class="req">*</span></label>
                    <input type="tel" id="phone" name="phone" class="input" required value="{{ old('phone') }}">
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label for="city">Ciudad <span class="req">*</span></label>
                    <input type="text" id="city" name="city" class="input" required value="{{ old('city') }}">
                </div>
                <div class="field">
                    <label for="professional_card">Tarjeta profesional <span class="req">*</span></label>
                    <input type="text" id="professional_card" name="professional_card" class="input" required
                           value="{{ old('professional_card') }}">
                    <span class="field__hint" data-hint-individual>Solo para verificarte. No se publica en el sitio.</span>
                    <span class="field__hint" data-hint-firm hidden>La del representante. Tampoco se publica.</span>
                </div>
            </div>

            <div class="form-row" data-only-firm hidden>
                <div class="field">
                    <label for="nit">NIT de la firma <span class="req">*</span></label>
                    <input type="text" id="nit" name="nit" class="input" value="{{ old('nit') }}">
                    <span class="field__hint">Para verificar que la firma existe. No se publica.</span>
                    <x-field-error name="nit" />
                </div>
                <div class="field">
                    <label for="team_size">¿Cuántos abogados son?</label>
                    <input type="number" id="team_size" name="team_size" class="input" min="1"
                           value="{{ old('team_size') }}">
                </div>
            </div>

            <div class="field">
                <label for="headline">Tu especialidad en una línea</label>
                <input type="text" id="headline" name="headline" class="input" value="{{ old('headline') }}"
                       placeholder="Especialista en derecho laboral y seguridad social">
            </div>

            <div class="field">
                <label>Áreas de práctica <span class="req">*</span></label>
                <div class="grid grid--3" style="gap: var(--s2);">
                    @foreach ($areas as $area)
                        <label class="checkbox">
                            <input type="checkbox" name="practice_areas[]" value="{{ $area->value }}"
                                   @checked(in_array($area->value, old('practice_areas', []), true))>
                            <span>{{ $area->emoji() }} {{ $area->getLabel() }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="field">
                <label for="lawyer_plan_id">Plan que te interesa <span class="req">*</span></label>
                <select id="lawyer_plan_id" name="lawyer_plan_id" class="select" required>
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}" @selected((int) old('lawyer_plan_id') === $plan->id)>
                            {{ $plan->name }} — {{ $cop($plan->price_monthly) }}/mes
                            @if ($plan->free_trial_months) ({{ $plan->free_trial_months }} meses gratis) @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <label class="checkbox">
                <input type="checkbox" name="accepts_terms" value="1" required @checked(old('accepts_terms'))>
                <span>
                    Acepto los <a href="{{ route('pages.terms') }}" target="_blank">términos</a> y el
                    <a href="{{ route('pages.privacy') }}" target="_blank">tratamiento de mis datos</a>.
                    <span data-label-individual>Declaro que la tarjeta profesional es mía y está vigente.</span>
                    <span data-label-firm hidden>Declaro que represento a la firma y que la tarjeta profesional indicada está vigente.</span>
                    <span class="req">*</span>
                </span>
            </label>

            <button type="submit" class="btn btn--primary btn--lg">Solicitar mi perfil</button>

            <p class="text-xs text-muted">
                Te escribimos en menos de 24 horas hábiles. La prueba gratis empieza cuando tu perfil se publica.
            </p>
        </form>

        <p class="center text-sm text-muted" style="margin-top: var(--s8);">
            ¿Ya haces parte de la red?
            <a href="{{ url('/portal') }}"><strong>Entra a tu portal</strong></a>
            para ver tus casos, tus contactos y tus resultados.
        </p>
    </div>
</section>
@endsection

@push('scripts')
<script>
/* Enseña solo lo que aplica a quien se registra. Preguntarle el NIT a alguien
   que ejerce solo es una forma rápida de perderlo. */
(function () {
    var radios = document.querySelectorAll('[data-kind-switch] input[name="kind"]');
    if (!radios.length) return;

    function sync() {
        var esFirma = document.querySelector('input[name="kind"]:checked')?.value === 'firm';

        document.querySelectorAll('[data-only-firm]').forEach(function (el) { el.hidden = !esFirma; });
        document.querySelectorAll('[data-only-individual]').forEach(function (el) { el.hidden = esFirma; });
        document.querySelectorAll('[data-label-firm], [data-hint-firm]').forEach(function (el) { el.hidden = !esFirma; });
        document.querySelectorAll('[data-label-individual], [data-hint-individual]').forEach(function (el) { el.hidden = esFirma; });

        /* Un campo oculto no puede ser obligatorio: el navegador se queja de
           algo que el usuario no ve. */
        document.querySelectorAll('[data-only-firm] input').forEach(function (input) {
            input.required = esFirma && (input.name === 'nit' || input.name === 'contact_name');
        });
    }

    radios.forEach(function (radio) { radio.addEventListener('change', sync); });
    sync();
})();
</script>
@endpush
