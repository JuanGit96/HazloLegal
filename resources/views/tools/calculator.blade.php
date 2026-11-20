@extends('layouts.site')

@section('content')
@php
    $cop = fn (int|float $n) => '$'.number_format((float) $n, 0, ',', '.');
@endphp

<section class="section section--white section--tight">
    <div class="container container--narrow">
        <x-breadcrumbs :items="$meta->breadcrumbs" />

        <div class="section-head" style="margin-bottom: var(--s8);">
            <span class="pill pill--success">Gratis · sin registro</span>
            <h1 style="margin-top: var(--s4);">{{ $calculator->emoji }} {{ $calculator->name }}</h1>
            <p>{{ $calculator->intro }}</p>
        </div>

        <div class="card card--pad-lg">
            <form action="{{ $calculator->url() }}" method="post" class="form">
                @csrf

                @if ($errors->any())
                    <div class="alert alert--danger">
                        <span class="alert__icon" aria-hidden="true">!</span>
                        <div>
                            <ul style="margin:0; padding-left: var(--s5);">
                                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @switch($calculator->key)
                    @case('liquidacion')
                        <div class="form-row">
                            <div class="field">
                                <label for="salary">Tu salario mensual <span class="req">*</span></label>
                                <input type="number" id="salary" name="salary" class="input" required min="1"
                                       value="{{ old('salary') }}" placeholder="2000000" inputmode="numeric">
                                <span class="field__hint">Sin puntos ni comas.</span>
                            </div>
                            <div class="field">
                                <label for="days">Días trabajados <span class="req">*</span></label>
                                <input type="number" id="days" name="days" class="input" required min="1"
                                       value="{{ old('days') }}" placeholder="660" inputmode="numeric">
                                <span class="field__hint">Un año son 360 días para efectos laborales.</span>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="field">
                                <label for="pending_vacation_days">Días de vacaciones pendientes</label>
                                <input type="number" id="pending_vacation_days" name="pending_vacation_days"
                                       class="input" min="0" value="{{ old('pending_vacation_days', 0) }}" inputmode="numeric">
                            </div>
                            <label class="checkbox" style="align-self: end; padding-bottom: var(--s3);">
                                <input type="checkbox" name="transport" value="1" @checked(old('transport', true))>
                                <span>Recibo auxilio de transporte</span>
                            </label>
                        </div>
                        @break

                    @case('indemnizacion')
                        <div class="form-row">
                            <div class="field">
                                <label for="salary">Tu salario mensual <span class="req">*</span></label>
                                <input type="number" id="salary" name="salary" class="input" required min="1"
                                       value="{{ old('salary') }}" inputmode="numeric">
                            </div>
                            <div class="field">
                                <label for="days">Días trabajados <span class="req">*</span></label>
                                <input type="number" id="days" name="days" class="input" required min="1"
                                       value="{{ old('days') }}" inputmode="numeric">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="field">
                                <label for="contract_type">Tipo de contrato <span class="req">*</span></label>
                                <select id="contract_type" name="contract_type" class="select" required>
                                    <option value="indefinite" @selected(old('contract_type')==='indefinite')>Término indefinido</option>
                                    <option value="fixed" @selected(old('contract_type')==='fixed')>Término fijo</option>
                                </select>
                            </div>
                            <div class="field">
                                <label for="remaining_days">Días que faltaban (solo término fijo)</label>
                                <input type="number" id="remaining_days" name="remaining_days" class="input"
                                       min="0" value="{{ old('remaining_days', 0) }}" inputmode="numeric">
                            </div>
                        </div>
                        @break

                    @case('horas_extras')
                        <div class="field">
                            <label for="salary">Tu salario mensual <span class="req">*</span></label>
                            <input type="number" id="salary" name="salary" class="input" required min="1"
                                   value="{{ old('salary') }}" inputmode="numeric">
                        </div>
                        <div class="form-row">
                            <div class="field">
                                <label for="extra_diurna">Horas extra diurnas</label>
                                <input type="number" id="extra_diurna" name="extra_diurna" class="input" min="0"
                                       value="{{ old('extra_diurna', 0) }}" inputmode="numeric">
                            </div>
                            <div class="field">
                                <label for="extra_nocturna">Horas extra nocturnas</label>
                                <input type="number" id="extra_nocturna" name="extra_nocturna" class="input" min="0"
                                       value="{{ old('extra_nocturna', 0) }}" inputmode="numeric">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="field">
                                <label for="recargo_nocturno">Horas con recargo nocturno</label>
                                <input type="number" id="recargo_nocturno" name="recargo_nocturno" class="input" min="0"
                                       value="{{ old('recargo_nocturno', 0) }}" inputmode="numeric">
                            </div>
                            <div class="field">
                                <label for="dominical">Horas dominicales o festivas</label>
                                <input type="number" id="dominical" name="dominical" class="input" min="0"
                                       value="{{ old('dominical', 0) }}" inputmode="numeric">
                            </div>
                        </div>
                        @break

                    @case('intereses_mora')
                        <div class="form-row">
                            <div class="field">
                                <label for="amount">Monto de la deuda <span class="req">*</span></label>
                                <input type="number" id="amount" name="amount" class="input" required min="1"
                                       value="{{ old('amount') }}" inputmode="numeric">
                            </div>
                            <div class="field">
                                <label for="days">Días de mora <span class="req">*</span></label>
                                <input type="number" id="days" name="days" class="input" required min="1"
                                       value="{{ old('days') }}" inputmode="numeric">
                            </div>
                        </div>
                        <p class="text-xs text-muted">
                            Tasa máxima legal usada: {{ $calculator->param('max_annual_rate', 25.5) }} % efectivo anual.
                            La certifica la Superintendencia Financiera y cambia cada mes.
                        </p>
                        @break
                @endswitch

                <button type="submit" class="btn btn--primary btn--lg btn--block">Calcular gratis</button>
            </form>
        </div>

        {{-- ---------------- Resultado ---------------- --}}
        @if ($result)
            <div class="card card--pad-lg mt-8" style="border: 2px solid var(--success); background: var(--green-50);">
                <span class="eyebrow" style="color: var(--green-700);">Resultado estimado</span>

                @if (isset($result['items']))
                    <dl class="summary__rows" style="margin-top: var(--s5);">
                        @foreach ($result['items'] as $label => $value)
                            <div class="summary__row">
                                <dt>{{ $label }}</dt>
                                <dd>{{ $cop($value) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif

                <div class="summary__total" style="margin-top: var(--s5);">
                    <span>{{ isset($result['interest']) ? 'Total con intereses' : 'Total estimado' }}</span>
                    <span>{{ $cop($result['total']) }}</span>
                </div>

                @if (isset($result['interest']))
                    <p class="text-sm text-muted" style="margin-top: var(--s3);">
                        Solo intereses: <strong>{{ $cop($result['interest']) }}</strong>
                    </p>
                @endif

                @if (isset($result['formula']))
                    <p class="text-sm" style="margin-top: var(--s4); color: var(--green-700);">
                        <strong>Cómo se calcula:</strong> {{ $result['formula'] }}
                    </p>
                @endif

                @if (! empty($result['notes']))
                    <ul class="text-sm text-muted" style="margin-top: var(--s4);">
                        @foreach ($result['notes'] as $note)<li>{{ $note }}</li>@endforeach
                    </ul>
                @endif

                <div class="alert alert--warn mt-6">
                    <span class="alert__icon" aria-hidden="true">ℹ️</span>
                    <div>
                        <strong>Es una estimación, no una liquidación oficial.</strong>
                        <p style="margin-top: var(--s1);">
                            Si recibías comisiones, bonificaciones habituales o pagos disfrazados de auxilio,
                            tu base real es mayor y esta cuenta se queda corta.
                        </p>
                    </div>
                </div>

                @if ($calculator->relatedDocument)
                    <div class="btn-row mt-6">
                        <a href="{{ $calculator->relatedDocument->url() }}" class="btn btn--primary">
                            Que un abogado revise mi caso
                        </a>
                        <a href="{{ route('lawyers.index') }}?area=laboral" class="btn btn--outline">
                            Buscar abogado laboral
                        </a>
                    </div>
                @endif
            </div>
        @endif

        @if ($calculator->body)
            <article class="prose mt-10">{!! $calculator->body !!}</article>
        @endif

        @if ($calculator->faqs)
            <div class="mt-10">
                <h2>Preguntas frecuentes</h2>
                <x-faq-list :faqs="$calculator->faqs" :open="1" />
            </div>
        @endif

        @if ($related->isNotEmpty())
            <div class="mt-10">
                <h2 style="font-size: var(--t-xl);">Otras calculadoras</h2>
                <div class="grid grid--3 mt-6">
                    @foreach ($related as $other)
                        <a href="{{ $other->url() }}" class="tool-card tool-card--light">
                            <span class="tool-card__emoji" aria-hidden="true">{{ $other->emoji }}</span>
                            <h3 style="font-size: var(--t-base);">{{ $other->name }}</h3>
                            <span class="tool-card__cta" style="color: var(--brand);">Calcular →</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
