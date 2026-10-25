<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\LegalCases\Enums\BudgetRange;
use App\Domain\LegalCases\Enums\CaseUrgency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCaseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'client_name' => ['required', 'string', 'min:5', 'max:120'],
            'client_email' => ['required', $this->emailRule(), 'max:160'],
            'client_phone' => ['required', 'string', 'min:7', 'max:30'],
            'client_city' => ['nullable', 'string', 'max:80'],
            'practice_area' => ['required', Rule::enum(PracticeArea::class)],
            'title' => ['required', 'string', 'min:10', 'max:160'],
            'description' => ['required', 'string', 'min:80', 'max:8000'],
            'urgency' => ['required', Rule::enum(CaseUrgency::class)],
            'budget_range' => ['nullable', Rule::enum(BudgetRange::class)],
            'has_documents' => ['nullable', 'boolean'],
            'deadline_note' => ['nullable', 'string', 'max:160'],
            'accepts_privacy' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.min' => 'Resume tu caso en una frase (mínimo 10 caracteres).',
            'description.min' => 'Los abogados deciden con esta información: escribe al menos 80 caracteres explicando qué pasó, cuándo y qué buscas.',
            'accepts_privacy.accepted' => 'Necesitamos tu autorización de tratamiento de datos para enviar tu caso a los abogados.',
        ];
    }

    public function attributes(): array
    {
        return [
            'client_name' => 'nombre',
            'client_email' => 'correo',
            'client_phone' => 'celular',
            'practice_area' => 'área del caso',
            'title' => 'resumen',
            'description' => 'descripción',
        ];
    }

    /** DNS sólo en producción: en local bloquearía los correos de prueba. */
    protected function emailRule(): string
    {
        return app()->isProduction() ? 'email:rfc,dns' : 'email:rfc';
    }
}
