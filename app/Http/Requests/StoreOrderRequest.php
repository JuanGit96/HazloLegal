<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Catalog\Services\DocumentFieldResolver;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Las preguntas propias del trámite se definen en el panel, así
            // que sus reglas se arman en tiempo de ejecución.
            ...app(DocumentFieldResolver::class)->rules($this->document()),

            'addons' => ['nullable', 'array'],
            'addons.*' => ['string', 'max:40'],

            'customer_name' => ['required', 'string', 'min:5', 'max:120'],
            'customer_email' => ['required', $this->emailRule(), 'max:160'],
            'customer_phone' => ['required', 'string', 'min:7', 'max:30'],
            'customer_document_type' => ['required', 'in:CC,CE,NIT,PA,PEP'],
            'customer_document_number' => ['nullable', 'string', 'max:30'],
            'customer_city' => ['nullable', 'string', 'max:80'],
            'case_summary' => ['required', 'string', 'min:30', 'max:4000'],

            'accepts_marketing' => ['nullable', 'boolean'],
            'shareable_with_lawyers' => ['nullable', 'boolean'],
            'payment_method' => ['nullable', 'string'],

            'accepts_terms' => ['accepted'],
            'accepts_privacy' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.min' => 'Escribe tu nombre completo, como aparece en tu documento.',
            'customer_email.email' => 'Revisa el correo: es a donde enviaremos el documento.',
            'case_summary.min' => 'Cuéntanos un poco más (al menos 30 caracteres) para que el documento quede bien.',
            'accepts_terms.accepted' => 'Necesitamos que aceptes los términos para continuar.',
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
            ...app(DocumentFieldResolver::class)->attributes($this->document()),
        ];
    }

    /** El trámite de la ruta, que es quien define las preguntas extra. */
    public function document(): LegalDocument
    {
        $document = $this->route('document');

        return $document instanceof LegalDocument
            ? $document
            : LegalDocument::query()->where('slug', (string) $document)->firstOrFail();
    }

    /** DNS sólo en producción: en local bloquearía los correos de prueba. */
    protected function emailRule(): string
    {
        return app()->isProduction() ? 'email:rfc,dns' : 'email:rfc';
    }
}
