<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Services;

use App\Domain\Catalog\Data\RiskAssessment;
use App\Domain\Catalog\Enums\RiskLevel;
use App\Domain\Catalog\Enums\RiskRuleKind;
use App\Domain\Catalog\Models\DocumentRiskRule;
use App\Domain\Catalog\Models\LegalDocument;
use Illuminate\Support\Str;

/**
 * Decide si un documento automático le sirve a esta persona.
 *
 * No mide gravedad jurídica. Mide si el caso se le queda corto al producto:
 * alguien hospitalizado no necesita un derecho de petición mejor redactado,
 * necesita una tutela, y venderle lo primero sería cobrarle por perder
 * tiempo.
 *
 * Dos capas, y el orden importa:
 *
 * 1. **Preguntas estructuradas.** Es la capa que manda, porque el usuario
 *    contesta a conciencia.
 * 2. **Palabras en el texto libre.** Red de seguridad. Una coincidencia
 *    **nunca bloquea sola**: dispara la pregunta para que sea el usuario
 *    quien confirme. Decidir por él a partir de una palabra suelta daría
 *    falsos positivos y se sentiría arbitrario.
 *
 * Calibración: una señal ámbar deja ámbar; dos o más suben a rojo; una roja
 * es roja pase lo que pase.
 */
final class RiskAssessor
{
    /**
     * @param  array<string, mixed>  $answers  Respuestas a las preguntas
     * @param  string  $freeText  Lo que el usuario escribió
     */
    public function assess(LegalDocument $document, array $answers = [], string $freeText = ''): RiskAssessment
    {
        $reglas = $document->riskRules()->where('is_active', true)->get();

        $bloqueadas = $this->blockedWords($reglas, $freeText);

        if ($bloqueadas !== []) {
            return new RiskAssessment(RiskLevel::Blocked, [], $bloqueadas);
        }

        $rojas = [];
        $ambares = [];

        foreach ($reglas->where('kind', RiskRuleKind::Question) as $regla) {
            if (! $this->answered($answers, $regla)) {
                continue;
            }

            if ($regla->level === RiskLevel::Red) {
                $rojas[] = $regla->prompt;
            } else {
                $ambares[] = $regla->prompt;
            }
        }

        // Las palabras solo suman una señal del mismo color: la confirmación
        // la da el usuario en la pregunta correspondiente.
        foreach ($this->matchedKeywords($reglas, $freeText) as $regla) {
            if ($regla->level === RiskLevel::Red) {
                $ambares[] = 'Mencionaste «'.$regla->prompt.'»';
            } else {
                $ambares[] = 'Mencionaste «'.$regla->prompt.'»';
            }
        }

        // «Menor de edad» sube a rojo solo si viene acompañada.
        foreach ($reglas->where('escalates_with_others', true) as $regla) {
            if ($this->answered($answers, $regla) && count($ambares) + count($rojas) > 1) {
                $rojas[] = $regla->prompt;
            }
        }

        if ($rojas !== []) {
            return new RiskAssessment(RiskLevel::Red, array_values(array_unique([...$rojas, ...$ambares])));
        }

        $ambares = array_values(array_unique($ambares));

        return match (true) {
            count($ambares) >= 2 => new RiskAssessment(RiskLevel::Red, $ambares),
            count($ambares) === 1 => new RiskAssessment(RiskLevel::Amber, $ambares),
            default => new RiskAssessment(RiskLevel::Green),
        };
    }

    private function answered(array $answers, DocumentRiskRule $regla): bool
    {
        $valor = $answers[$regla->key] ?? null;

        return filter_var($valor, FILTER_VALIDATE_BOOLEAN);
    }

    /** @return array<int, string> */
    private function blockedWords($reglas, string $texto): array
    {
        $normalizado = $this->normalize($texto);

        return $reglas->where('kind', RiskRuleKind::Blocked)
            ->filter(fn (DocumentRiskRule $r) => $this->contains($normalizado, $r->prompt))
            ->pluck('prompt')
            ->values()
            ->all();
    }

    private function matchedKeywords($reglas, string $texto)
    {
        $normalizado = $this->normalize($texto);

        return $reglas->where('kind', RiskRuleKind::Keyword)
            ->filter(fn (DocumentRiskRule $r) => $this->contains($normalizado, $r->prompt));
    }

    /** Sin tildes ni mayúsculas: «cáncer» y «cancer» son la misma señal. */
    private function normalize(string $texto): string
    {
        return ' '.Str::of($texto)->lower()->ascii()->replaceMatches('/[^a-z0-9 ]+/', ' ')
            ->replaceMatches('/\s+/', ' ')->toString().' ';
    }

    private function contains(string $texto, string $termino): bool
    {
        $t = Str::of($termino)->lower()->ascii()->replaceMatches('/[^a-z0-9 ]+/', ' ')
            ->replaceMatches('/\s+/', ' ')->trim()->toString();

        return $t !== '' && str_contains($texto, ' '.$t.' ');
    }
}
