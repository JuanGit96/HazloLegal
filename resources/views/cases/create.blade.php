@extends('layouts.site')

@section('content')
@php $wa = app(\App\Domain\Shared\Services\WhatsAppLinkBuilder::class); @endphp

<section class="hero" style="padding-block: var(--s12) var(--s10);">
    <div class="container">
        <x-breadcrumbs :items="$meta->breadcrumbs" />

        <div class="hero__grid">
            <div>
                <div class="hero__kicker">
                    <span class="pill pill--success">✓ Cotización sin costo</span>
                    <span class="pill pill--brand">{{ $lawyerCount }} abogados en la red</span>
                </div>

                <h1>Tu caso complejo, en manos de <span class="accent">un abogado que lo acepta</span></h1>

                <p class="hero__lead">
                    Cuéntanos qué está pasando. Enviamos tu caso a los abogados especializados en esa
                    área al mismo tiempo y <strong>el primero que lo acepta se queda con él</strong>.
                    Te escribimos con su nombre, su especialidad y su experiencia.
                </p>

                <div class="hero__proof">
                    <div class="hero__proof-item">
                        <strong>&lt; 48 h</strong>
                        <span>para asignarte abogado</span>
                    </div>
                    <div class="hero__proof-item">
                        <strong>$0</strong>
                        <span>cuesta la cotización</span>
                    </div>
                    <div class="hero__proof-item">
                        <strong>0</strong>
                        <span>compromiso si no te convence</span>
                    </div>
                </div>
            </div>

            <div>
                <div class="card card--pad-lg" style="border-color: var(--brand);">
                    <h2 style="font-size: var(--t-lg);">Cómo funciona</h2>
                    <div class="steps" style="grid-template-columns: 1fr; gap: var(--s5); margin-top: var(--s5);">
                        <div class="step">
                            <div class="step__num" aria-hidden="true">1</div>
                            <h3>Llenas el formulario</h3>
                            <p>Cinco minutos. Mientras más contexto, mejor la cotización.</p>
                        </div>
                        <div class="step">
                            <div class="step__num" aria-hidden="true">2</div>
                            <h3>Lo enviamos a los especialistas</h3>
                            <p>A todos los abogados del área al mismo tiempo, no uno por uno.</p>
                        </div>
                        <div class="step">
                            <div class="step__num" aria-hidden="true">3</div>
                            <h3>El primero que acepta lo toma</h3>
                            <p>Y te llega su perfil completo por correo: especialidad, años de ejercicio y ciudad.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section section--white">
    <div class="container container--narrow">
        <div class="section-head">
            <h2>Cuéntanos tu caso</h2>
            <p>Los abogados deciden con esta información. Sé concreto con fechas y cifras.</p>
        </div>

        @if ($errors->any())
            <div class="alert alert--danger" style="margin-bottom: var(--s8);">
                <span class="alert__icon" aria-hidden="true">!</span>
                <div>
                    <strong>Faltan algunos datos:</strong>
                    <ul style="margin: var(--s2) 0 0; padding-left: var(--s5);">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('cases.store') }}" method="post" class="form">
            @csrf

            <div class="field">
                <label for="practice_area">¿De qué se trata tu caso? <span class="req" aria-hidden="true">*</span></label>
                <select id="practice_area" name="practice_area" class="select" required
                        aria-invalid="{{ $errors->has('practice_area') ? 'true' : 'false' }}">
                    <option value="">Elige el área…</option>
                    @foreach ($areas as $area)
                        <option value="{{ $area->value }}"
                            @selected(old('practice_area', $preselectedArea?->value) === $area->value)>
                            {{ $area->emoji() }} {{ $area->getLabel() }}
                        </option>
                    @endforeach
                </select>
                <span class="field__hint">Esto define a qué especialistas le llega tu caso.</span>
                <x-field-error name="practice_area" />
            </div>

            <div class="field">
                <label for="title">Resume tu caso en una frase <span class="req" aria-hidden="true">*</span></label>
                <input type="text" id="title" name="title" class="input" required minlength="10" maxlength="160"
                       value="{{ old('title') }}"
                       placeholder="Ejemplo: sucesión de mi padre con cuatro herederos y un inmueble en disputa"
                       aria-invalid="{{ $errors->has('title') ? 'true' : 'false' }}">
                <x-field-error name="title" />
            </div>

            <div class="field">
                <label for="description">
                    Explica qué pasó, cuándo y qué buscas <span class="req" aria-hidden="true">*</span>
                </label>
                <textarea id="description" name="description" class="textarea" required minlength="80" maxlength="8000"
                          style="min-height: 220px;" aria-describedby="desc_hint"
                          aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}"
                          placeholder="Incluye: qué ocurrió y en qué fechas, quiénes están involucrados, qué documentos tienes, si hay procesos o demandas en curso, y qué resultado buscas.">{{ old('description') }}</textarea>
                <span class="field__hint" id="desc_hint">
                    Mínimo 80 caracteres. Esta es la información con la que un abogado decide si puede ayudarte.
                </span>
                <x-field-error name="description" />
            </div>

            <div class="form-row">
                <div class="field">
                    <label for="urgency">¿Qué tan urgente es? <span class="req" aria-hidden="true">*</span></label>
                    <select id="urgency" name="urgency" class="select" required>
                        @foreach ($urgencies as $urgency)
                            <option value="{{ $urgency->value }}" @selected(old('urgency', 'normal') === $urgency->value)>
                                {{ $urgency->getLabel() }}
                            </option>
                        @endforeach
                    </select>
                    <x-field-error name="urgency" />
                </div>

                <div class="field">
                    <label for="budget_range">¿Qué presupuesto tienes en mente?</label>
                    <select id="budget_range" name="budget_range" class="select">
                        <option value="">Prefiero no decirlo todavía</option>
                        @foreach ($budgets as $budget)
                            <option value="{{ $budget->value }}" @selected(old('budget_range') === $budget->value)>
                                {{ $budget->getLabel() }}
                            </option>
                        @endforeach
                    </select>
                    <span class="field__hint">Ayuda a que te propongan algo realista. No es un compromiso.</span>
                </div>
            </div>

            <div class="field">
                <label for="deadline_note">¿Hay algún término o fecha límite?</label>
                <input type="text" id="deadline_note" name="deadline_note" class="input" maxlength="160"
                       value="{{ old('deadline_note') }}"
                       placeholder="Ejemplo: la audiencia es el 14 de noviembre">
            </div>

            <label class="checkbox">
                <input type="checkbox" name="has_documents" value="1" @checked(old('has_documents'))>
                <span>Tengo documentos del caso (contratos, demandas, fallos, historia clínica, etc.)</span>
            </label>

            <hr>

            <h3 style="font-size: var(--t-lg); margin-bottom: 0;">Cómo te contactamos</h3>

            <div class="form-row">
                <div class="field">
                    <label for="client_name">Nombre completo <span class="req" aria-hidden="true">*</span></label>
                    <input type="text" id="client_name" name="client_name" class="input" required
                           value="{{ old('client_name') }}" autocomplete="name"
                           aria-invalid="{{ $errors->has('client_name') ? 'true' : 'false' }}">
                    <x-field-error name="client_name" />
                </div>

                <div class="field">
                    <label for="client_email">Correo electrónico <span class="req" aria-hidden="true">*</span></label>
                    <input type="email" id="client_email" name="client_email" class="input" required
                           value="{{ old('client_email') }}" autocomplete="email"
                           aria-describedby="client_email_hint"
                           aria-invalid="{{ $errors->has('client_email') ? 'true' : 'false' }}">
                    <span class="field__hint" id="client_email_hint">Aquí te avisamos qué abogado tomó tu caso.</span>
                    <x-field-error name="client_email" />
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label for="client_phone">Celular (WhatsApp) <span class="req" aria-hidden="true">*</span></label>
                    <input type="tel" id="client_phone" name="client_phone" class="input" required
                           value="{{ old('client_phone') }}" autocomplete="tel" placeholder="300 123 4567"
                           aria-invalid="{{ $errors->has('client_phone') ? 'true' : 'false' }}">
                    <x-field-error name="client_phone" />
                </div>

                <div class="field">
                    <label for="client_city">Ciudad</label>
                    <input type="text" id="client_city" name="client_city" class="input"
                           value="{{ old('client_city') }}" autocomplete="address-level2">
                </div>
            </div>

            <label class="checkbox">
                <input type="checkbox" name="accepts_privacy" value="1" required @checked(old('accepts_privacy'))>
                <span>
                    Autorizo que mi caso se comparta con los abogados de la red de HazloLegal para que lo
                    evalúen, y el tratamiento de mis datos conforme a la
                    <a href="{{ route('pages.privacy') }}" target="_blank">política de privacidad</a>
                    y la Ley 1581 de 2012. <span class="req" aria-hidden="true">*</span>
                </span>
            </label>
            <x-field-error name="accepts_privacy" />

            <div class="alert alert--info">
                <span class="alert__icon" aria-hidden="true">🔒</span>
                <div>
                    <strong>Tu caso solo lo ven abogados titulados de nuestra red.</strong>
                    <p style="margin-top: var(--s1);">
                        Están sujetos a reserva profesional. No publicamos tu información ni la compartimos
                        con terceros, y puedes pedir su eliminación cuando quieras.
                    </p>
                </div>
            </div>

            <button type="submit" class="btn btn--primary btn--lg">Enviar mi caso a los abogados</button>

            <p class="text-xs text-muted">
                Enviar el caso no cuesta nada y no te compromete.
                Si prefieres hablar primero, escríbenos por
                <a href="{{ $wa->link('Hola, tengo un caso complejo y quiero preguntar algo antes de enviarlo.') }}" target="_blank" rel="noopener">WhatsApp</a>.
            </p>
        </form>
    </div>
</section>

@if ($team->isNotEmpty())
<section class="section section--sunken">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">La red</span>
            <h2>Algunos de los abogados que podrían tomar tu caso</h2>
            <p>Abogados colombianos en ejercicio, especializados por área.</p>
        </div>

        <div class="grid grid--4">
            @foreach ($team as $lawyer)
                <x-lawyer-card :lawyer="$lawyer" />
            @endforeach
        </div>
    </div>
</section>
@endif

@if ($faqs->isNotEmpty())
<section class="section section--white">
    <div class="container container--narrow">
        <div class="section-head">
            <span class="eyebrow">Preguntas frecuentes</span>
            <h2>Sobre los casos complejos</h2>
        </div>
        <x-faq-list :faqs="$faqs" :open="1" />
    </div>
</section>
@endif
@endsection
