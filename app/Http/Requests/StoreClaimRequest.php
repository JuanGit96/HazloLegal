<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Shared\Enums\ClaimReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClaimRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'client_name' => ['required', 'string', 'min:5', 'max:120'],
            'client_email' => ['required', $this->emailRule(), 'max:160'],
            'order_reference' => ['required', 'string', 'max:40'],
            'reason' => ['required', Rule::enum(ClaimReason::class)],
            'description' => ['required', 'string', 'min:30', 'max:4000'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_reference.required' => 'Escribe la referencia de tu pedido (aparece en tu correo, por ejemplo HL-2026-00042).',
            'description.min' => 'Cuéntanos qué salió mal con al menos 30 caracteres para poder corregirlo.',
        ];
    }

    /** DNS sólo en producción: en local bloquearía los correos de prueba. */
    protected function emailRule(): string
    {
        return app()->isProduction() ? 'email:rfc,dns' : 'email:rfc';
    }
}
