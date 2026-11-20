<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Data;

/**
 * Una pregunta propia de un trámite.
 *
 * Existe porque «cuéntanos tu caso» en una caja de texto produce documentos
 * genéricos: un derecho de petición necesita saber a qué entidad va, qué
 * pasó y cuándo, y eso no se puede adivinar de un párrafo libre.
 *
 * Se definen desde el panel, sin tocar código, y se usan en la plantilla
 * como {{ campo.clave }}.
 */
final readonly class DocumentField
{
    public function __construct(
        public string $key,
        public string $label,
        public string $type = 'text',
        public bool $required = false,
        public ?string $help = null,
        public ?string $placeholder = null,
    ) {}

    public static function fromArray(array $data): ?self
    {
        $key = trim((string) ($data['key'] ?? ''));
        $label = trim((string) ($data['label'] ?? ''));

        // Un campo sin clave o sin pregunta no se puede pintar ni guardar.
        if ($key === '' || $label === '') {
            return null;
        }

        return new self(
            key: $key,
            label: $label,
            type: in_array($data['type'] ?? 'text', ['text', 'textarea', 'date', 'number'], true)
                ? $data['type']
                : 'text',
            required: (bool) ($data['required'] ?? false),
            help: $data['help'] ?? null,
            placeholder: $data['placeholder'] ?? null,
        );
    }

    /** El nombre del campo en el formulario. */
    public function inputName(): string
    {
        return "fields[{$this->key}]";
    }

    public function inputId(): string
    {
        return 'field-'.$this->key;
    }

    /** @return array<int, string> */
    public function rules(): array
    {
        $rules = [$this->required ? 'required' : 'nullable'];

        $rules[] = match ($this->type) {
            'date' => 'date',
            'number' => 'numeric',
            'textarea' => 'string',
            default => 'string',
        };

        if (in_array($this->type, ['text', 'textarea'], true)) {
            $rules[] = $this->type === 'textarea' ? 'max:2000' : 'max:255';
        }

        return $rules;
    }

    /** Cómo se escribe el valor dentro del documento. */
    public function format(mixed $value): string
    {
        if (blank($value)) {
            return '';
        }

        if ($this->type === 'date') {
            try {
                return \Carbon\CarbonImmutable::parse((string) $value)
                    ->translatedFormat('j \d\e F \d\e Y');
            } catch (\Throwable) {
                return (string) $value;
            }
        }

        return (string) $value;
    }
}
