<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Catalog\Enums\DeliverySpeed;
use App\Domain\Orders\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'speed' => ['required', Rule::enum(DeliverySpeed::class)],
            'customer_name' => ['required', 'string', 'min:5', 'max:120'],
            'customer_email' => ['required', $this->emailRule(), 'max:160'],
            'customer_phone' => ['required', 'string', 'min:7', 'max:30'],
            'customer_document_type' => ['required', 'in:CC,CE,NIT,PA,PEP'],
            'customer_document_number' => ['nullable', 'string', 'max:30'],
            'customer_city' => ['nullable', 'string', 'max:80'],
            'case_summary' => ['required', 'string', 'min:30', 'max:4000'],
            'wants_signature' => ['nullable', 'boolean'],
            'payment_method' => ['nullable', Rule::enum(PaymentMethod::class)],
            'accepts_terms' => ['accepted'],
            'accepts_privacy' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.min' => 'Escribe tu nombre completo, como aparece en tu documento.',
            'customer_email.email' => 'Revisa el correo: es a donde enviaremos el documento.',
            'case_summary.min' => 'Cuéntanos un poco más (al menos 30 caracteres) para que el abogado entienda tu caso.',
            'accepts_terms.accepted' => 'Necesitamos que aceptes los términos para poder continuar.',
            'accepts_privacy.accepted' => 'Necesitamos tu autorización de tratamiento de datos (Ley 1581 de 2012).',
        ];
    }

    public function attributes(): array
    {
        return [
            'customer_name' => 'nombre',
            'customer_email' => 'correo',
            'customer_phone' => 'celular',
            'case_summary' => 'descripción del caso',
        ];
    }

    /** DNS sólo en producción: en local bloquearía los correos de prueba. */
    protected function emailRule(): string
    {
        return app()->isProduction() ? 'email:rfc,dns' : 'email:rfc';
    }
}
