<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'email' => ['required', app()->isProduction() ? 'email:rfc,dns' : 'email:rfc', 'max:160'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['nullable', 'string', 'max:160'],
            'message' => ['required', 'string', 'min:15', 'max:4000'],
            'accepts_privacy' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'message.min' => 'Cuéntanos un poco más para poder ayudarte.',
            'accepts_privacy.accepted' => 'Necesitamos tu autorización para tratar tus datos y poder responderte.',
        ];
    }
}
