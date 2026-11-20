<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Solicitud de supresión de datos (Ley 1581 de 2012).
 *
 * Se pide el documento de identidad porque hay que poder comprobar que quien
 * pide borrar los datos es el titular: atender una supresión sin verificar
 * quién la pide sería el problema contrario.
 */
class StoreDataDeletionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:5', 'max:120'],
            'email' => ['required', app()->isProduction() ? 'email:rfc,dns' : 'email:rfc', 'max:160'],
            'phone' => ['nullable', 'string', 'max:30'],
            'document_type' => ['required', 'in:CC,CE,NIT,PA,PEP'],
            'document_number' => ['required', 'string', 'max:30'],
            'message' => ['nullable', 'string', 'max:4000'],
            'confirms_ownership' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Necesitamos el correo con el que nos diste tus datos.',
            'document_number.required' => 'Necesitamos tu número de documento para comprobar que eres el titular.',
            'confirms_ownership.accepted' => 'Debes declarar que eres el titular de los datos que pides eliminar.',
        ];
    }
}
