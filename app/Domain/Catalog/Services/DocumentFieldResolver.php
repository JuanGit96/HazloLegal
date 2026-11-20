<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Services;

use App\Domain\Catalog\Data\DocumentField;
use App\Domain\Catalog\Models\LegalDocument;
use Illuminate\Support\Collection;

/**
 * Las preguntas propias de un trámite, ya normalizadas.
 *
 * Lo que llega de la base es JSON que escribió alguien en el panel, así que
 * aquí se filtra lo que no sirve antes de que llegue a una vista o a una
 * regla de validación.
 */
final class DocumentFieldResolver
{
    /** @return Collection<int, DocumentField> */
    public function for(LegalDocument $document): Collection
    {
        return collect($document->template_fields ?? [])
            ->map(fn ($row) => is_array($row) ? DocumentField::fromArray($row) : null)
            ->filter()
            ->values();
    }

    /** Reglas de validación para el formulario del checkout. */
    public function rules(LegalDocument $document): array
    {
        $rules = ['fields' => ['nullable', 'array']];

        foreach ($this->for($document) as $field) {
            $rules["fields.{$field->key}"] = $field->rules();
        }

        return $rules;
    }

    /** Nombres legibles, para que el error diga la pregunta y no «fields.entidad». */
    public function attributes(LegalDocument $document): array
    {
        return $this->for($document)
            ->mapWithKeys(fn (DocumentField $field) => [
                "fields.{$field->key}" => mb_strtolower($field->label),
            ])
            ->all();
    }

    /**
     * Lo que se guarda con el pedido.
     *
     * Se queda con la pregunta además de la respuesta: dentro de seis meses,
     * un pedido que solo guarde `['entidad' => 'Sura']` no dice qué se
     * preguntó, y la plantilla puede haber cambiado.
     */
    public function capture(LegalDocument $document, array $input): array
    {
        return $this->for($document)
            ->mapWithKeys(fn (DocumentField $field) => [
                $field->key => [
                    'label' => $field->label,
                    'type' => $field->type,
                    'value' => $input[$field->key] ?? null,
                ],
            ])
            ->filter(fn (array $row) => filled($row['value']))
            ->all();
    }
}
