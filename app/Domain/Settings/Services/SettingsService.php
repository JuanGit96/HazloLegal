<?php

declare(strict_types=1);

namespace App\Domain\Settings\Services;

use App\Domain\Settings\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

/**
 * Acceso a los ajustes que el equipo cambia desde el panel.
 *
 * Se cachea en memoria durante la petición y en el caché de la aplicación
 * entre peticiones: estos valores se leen en casi todas las vistas y no
 * tiene sentido pegarle a la base por cada logo.
 */
final class SettingsService
{
    private const CACHE_KEY = 'site_settings';

    private ?array $loaded = null;

    public function all(): array
    {
        return $this->loaded ??= Cache::rememberForever(
            self::CACHE_KEY,
            fn () => SiteSetting::query()
                ->get()
                ->mapWithKeys(fn (SiteSetting $s) => [$s->key => $s->typedValue()])
                ->all()
        );
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function bool(string $key, bool $default = false): bool
    {
        return (bool) $this->get($key, $default);
    }

    public function int(string $key, int $default = 0): int
    {
        return (int) $this->get($key, $default);
    }

    /** Ruta pública de una imagen configurable, con respaldo al asset del repo. */
    public function image(string $key, string $fallback): string
    {
        $value = $this->get($key);

        return filled($value) ? asset('storage/'.ltrim((string) $value, '/')) : asset($fallback);
    }

    public function set(string $key, mixed $value): void
    {
        SiteSetting::query()->where('key', $key)->update([
            'value' => is_array($value) ? json_encode($value) : $value,
        ]);

        $this->flush();
    }

    public function flush(): void
    {
        $this->loaded = null;
        Cache::forget(self::CACHE_KEY);
    }
}
