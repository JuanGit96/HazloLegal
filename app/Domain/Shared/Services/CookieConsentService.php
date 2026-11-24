<?php

declare(strict_types=1);

namespace App\Domain\Shared\Services;

use App\Domain\Shared\Data\CookieConsentChoice;
use App\Domain\Shared\Enums\CookieCategory;
use App\Domain\Shared\Models\CookieConsent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

/**
 * Quién decide si corre una cookie que no es imprescindible.
 *
 * La decisión se guarda dos veces y por motivos distintos: en una cookie,
 * porque es lo único que el navegador consulta en cada visita, y en una fila,
 * porque la ley obliga a poder demostrar la autorización y el visitante
 * puede borrar su navegador.
 *
 * La regla que no se negocia: sin respuesta, lo opcional no corre. Cargar
 * analítica «mientras decide» convertiría el banner en decoración.
 */
final class CookieConsentService
{
    public const COOKIE = 'hl_cookies';

    /**
     * Lo que este visitante tiene decidido ahora mismo.
     *
     * La petición se pide al contenedor en cada llamada en vez de inyectarla
     * en el constructor: este servicio se resuelve también desde las vistas,
     * y una petición guardada al construirlo puede no ser la de ahora.
     */
    public function current(): CookieConsentChoice
    {
        $raw = $this->request()->cookie(self::COOKIE);

        if (! is_string($raw) || $raw === '') {
            return CookieConsentChoice::pending();
        }

        $data = json_decode($raw, true);

        return is_array($data)
            ? CookieConsentChoice::fromArray($data)
            : CookieConsentChoice::pending();
    }

    public function allows(CookieCategory|string $category): bool
    {
        return $this->current()->allows($category);
    }

    /** ¿Se le pinta el banner? */
    public function shouldAsk(): bool
    {
        return $this->current()->needsAsking($this->version());
    }

    /**
     * Guarda la decisión: la fila primero, la cookie después.
     *
     * Si fallara el guardado no queremos una cookie que diga «autorizado»
     * sin respaldo, así que el orden importa.
     */
    public function remember(bool $analytics, bool $marketing, string $source = 'banner'): CookieConsentChoice
    {
        $token = $this->current()->token ?: (string) Str::uuid();

        CookieConsent::query()->create([
            'token' => $token,
            'analytics' => $analytics,
            'marketing' => $marketing,
            'decision' => $this->describe($analytics, $marketing),
            'policy_version' => $this->version(),
            'ip' => $this->request()->ip(),
            'user_agent' => Str::limit((string) $this->request()->userAgent(), 390, ''),
            'source' => $source,
        ]);

        $choice = new CookieConsentChoice(
            analytics: $analytics,
            marketing: $marketing,
            token: $token,
            policyVersion: $this->version(),
            answered: true,
        );

        Cookie::queue(Cookie::make(
            name: self::COOKIE,
            value: (string) json_encode($choice->toArray()),
            minutes: $this->lifetimeDays() * 24 * 60,
        ));

        return $choice;
    }

    /**
     * El visitante revoca lo que había autorizado.
     *
     * Queda una fila con la revocación —hay que poder demostrar también
     * cuándo dejó de haber permiso— y la cookie pasa a decir «solo las
     * necesarias». Las que ya estén puestas en el navegador se borran
     * aparte, porque el servidor no puede alcanzar las de terceros.
     */
    public function withdraw(): CookieConsentChoice
    {
        return $this->remember(false, false, 'preferencias');
    }

    /** Qué nombre le ponemos a la decisión, para leer el registro después. */
    private function describe(bool $analytics, bool $marketing): string
    {
        return match (true) {
            $analytics && $marketing => 'all',
            ! $analytics && ! $marketing => 'necessary',
            default => 'custom',
        };
    }

    /**
     * La versión del texto que el visitante aceptó.
     *
     * Si cambia lo que hacemos con las cookies, se sube la versión y se
     * vuelve a preguntar: una autorización dada sobre otro texto no vale
     * para el nuevo.
     */
    public function version(): string
    {
        return (string) config('hazlolegal.cookies.version', '1');
    }

    private function request(): Request
    {
        return request();
    }

    public function lifetimeDays(): int
    {
        return (int) config('hazlolegal.cookies.lifetime_days', 365);
    }

    /** El catálogo que se publica en la política, agrupado por familia. */
    public function catalog(): array
    {
        // El nombre de la cookie de sesión lo decide config/session.php, que
        // no está cargado cuando se lee la configuración de negocio.
        $catalog = array_map(
            fn (array $cookie) => [...$cookie, 'name' => str_replace(
                '{session}', (string) config('session.cookie'), (string) ($cookie['name'] ?? '')
            )],
            (array) config('hazlolegal.cookies.catalog', []),
        );
        $grouped = [];

        foreach (CookieCategory::cases() as $category) {
            $grouped[$category->value] = [
                'category' => $category,
                'cookies' => array_values(array_filter(
                    $catalog,
                    fn (array $cookie) => ($cookie['category'] ?? null) === $category->value,
                )),
            ];
        }

        return $grouped;
    }

    /** ¿Hay algo de verdad que cargar si lo autoriza? */
    public function hasAnalyticsConfigured(): bool
    {
        return filled(config('hazlolegal.seo.ga_measurement_id'));
    }

    public function hasMarketingConfigured(): bool
    {
        return filled(config('hazlolegal.seo.meta_pixel_id'));
    }
}
