<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:120'],
            'email' => ['required', $this->emailRule(), 'max:160'],
            'accepts_privacy' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'accepts_privacy.accepted' => 'Necesitamos tu autorización para enviarte la plantilla por correo.',
        ];
    }

    /** DNS sólo en producción: en local bloquearía los correos de prueba. */
    protected function emailRule(): string
    {
        return app()->isProduction() ? 'email:rfc,dns' : 'email:rfc';
    }
}
