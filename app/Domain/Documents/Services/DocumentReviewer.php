<?php

declare(strict_types=1);

namespace App\Domain\Documents\Services;

// OJO: esto NO es inteligencia artificial. Son reglas deterministas. Mientras
// siga siéndolo, ningún texto del sitio puede decir que una IA corrige el
// documento: sería prometer algo que el producto no hace.

use App\Domain\Documents\Data\ReviewResult;
use Illuminate\Support\Str;

/**
 * Revisión automática del texto que escribe el cliente.
 *
 * Es un gancho comercial («revisamos tu redacción») pero sobre todo
 * es defensa: un derecho de petición con groserías, en mayúsculas sostenidas
 * o sin signos de puntuación se gana el rechazo de la entidad antes de que
 * nadie lea el fondo del asunto.
 *
 * Esta implementación es determinista: corrige sin depender de un servicio
 * externo, sin costo por documento y sin mandar los datos del ciudadano a
 * ningún tercero. Para el documento gratuito eso importa: a volumen alto, un
 * modelo por cada texto costaría más que el ingreso del trámite.
 *
 * Si más adelante se quiere una revisión más fina, el punto de extensión está
 * documentado en `docs/02-arquitectura/ia-revision.md`.
 */
final class DocumentReviewer
{
    /** Palabras que nunca deben llegar a una entidad. */
    private const PROFANITY = [
        'hijueputa', 'hp', 'malparido', 'gonorrea', 'marica', 'maricón',
        'puta', 'puto', 'mierda', 'carajo', 'imbécil', 'estúpido', 'idiota',
        'ladrones', 'ratas', 'sinvergüenzas', 'corruptos',
    ];

    /** Muletillas que restan seriedad al escrito. */
    private const FILLERS = [
        'o sea', 'pues', 'la verdad es que', 'para serle sincero',
        'no sé si me explico', 'como le digo',
    ];

    public function review(string $text): ReviewResult
    {
        $original = $text;
        $issues = [];
        $warnings = [];

        $text = $this->normalizeWhitespace($text, $issues);
        $text = $this->fixShouting($text, $issues);
        $text = $this->removeProfanity($text, $issues);
        $text = $this->removeFillers($text, $issues);
        $text = $this->fixSentenceCasing($text, $issues);
        $text = $this->fixPunctuation($text, $issues);

        $warnings = $this->contentWarnings($text);

        return new ReviewResult(
            original: $original,
            corrected: trim($text),
            issues: array_values(array_unique($issues)),
            warnings: $warnings,
            usedAi: false,
        );
    }

    private function normalizeWhitespace(string $text, array &$issues): string
    {
        $before = $text;
        $text = preg_replace('/[ \t]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/\n{3,}/u', "\n\n", $text) ?? $text;
        $text = preg_replace('/ +\n/u', "\n", $text) ?? $text;

        if ($before !== $text) {
            $issues[] = 'Ajustamos los espacios y saltos de línea.';
        }

        return $text;
    }

    /** MAYÚSculas SOSTENIDAS: en un escrito formal se leen como un grito. */
    private function fixShouting(string $text, array &$issues): string
    {
        $letters = preg_replace('/[^\p{L}]/u', '', $text) ?? '';

        if (mb_strlen($letters) < 20) {
            return $text;
        }

        $upper = preg_replace('/[^\p{Lu}]/u', '', $letters) ?? '';

        if (mb_strlen($upper) / mb_strlen($letters) > 0.6) {
            $issues[] = 'Quitamos las mayúsculas sostenidas: en un escrito formal se leen como un grito.';

            return mb_convert_case(mb_strtolower($text), MB_CASE_TITLE_SIMPLE === 0 ? MB_CASE_TITLE : MB_CASE_TITLE);
        }

        return $text;
    }

    private function removeProfanity(string $text, array &$issues): string
    {
        $found = false;

        foreach (self::PROFANITY as $word) {
            $pattern = '/\b'.preg_quote($word, '/').'\b/iu';

            if (preg_match($pattern, $text)) {
                $found = true;
                $text = preg_replace($pattern, '', $text) ?? $text;
            }
        }

        if ($found) {
            $issues[] = 'Retiramos expresiones ofensivas. Una entidad puede archivar un escrito irrespetuoso sin entrar al fondo.';
            $text = preg_replace('/\s{2,}/u', ' ', $text) ?? $text;
        }

        return $text;
    }

    private function removeFillers(string $text, array &$issues): string
    {
        $before = $text;

        foreach (self::FILLERS as $filler) {
            $text = preg_replace('/\b'.preg_quote($filler, '/').'\b,?\s*/iu', '', $text) ?? $text;
        }

        if ($before !== $text) {
            $issues[] = 'Quitamos muletillas para que el escrito suene formal.';
        }

        return $text;
    }

    /** Primera letra de cada frase en mayúscula. */
    private function fixSentenceCasing(string $text, array &$issues): string
    {
        $before = $text;

        $text = preg_replace_callback(
            '/(^|[.!?]\s+|\n)(\p{Ll})/u',
            fn (array $m) => $m[1].mb_strtoupper($m[2]),
            $text
        ) ?? $text;

        if ($before !== $text) {
            $issues[] = 'Corregimos el uso de mayúsculas al inicio de cada frase.';
        }

        return $text;
    }

    private function fixPunctuation(string $text, array &$issues): string
    {
        $before = $text;

        // Espacio antes de signo de cierre, y falta de espacio después.
        $text = preg_replace('/\s+([,.;:!?])/u', '$1', $text) ?? $text;
        $text = preg_replace('/([,.;:])(\p{L})/u', '$1 $2', $text) ?? $text;
        $text = preg_replace('/([.!?]){2,}/u', '$1', $text) ?? $text;

        // Cierra la frase si el cliente no lo hizo.
        if (filled($text) && ! preg_match('/[.!?]\s*$/u', $text)) {
            $text .= '.';
        }

        if ($before !== $text) {
            $issues[] = 'Ajustamos la puntuación.';
        }

        return $text;
    }

    /**
     * Lo que no corregimos pero conviene advertir.
     *
     * No se toca el contenido: si falta una fecha, la pone el cliente. Añadir
     * datos que nadie dio sería inventar hechos en un documento legal.
     */
    private function contentWarnings(string $text): array
    {
        $warnings = [];
        $words = str_word_count(strip_tags($text));

        if ($words < 25) {
            $warnings[] = 'El relato es muy corto. Entre más detalle des, más sólido queda el documento.';
        }

        if (! preg_match('/\b\d{1,2}\s+de\s+\p{L}+|\b\d{1,2}\/\d{1,2}\/\d{2,4}|\b(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre)\b/iu', $text)) {
            $warnings[] = 'No mencionaste ninguna fecha. Las fechas concretas son lo que vuelve exigible un reclamo.';
        }

        if (! preg_match('/\b\d{3,}\b/u', $text)) {
            $warnings[] = 'No hay números de referencia (radicado, contrato, factura). Si los tienes, agrégalos.';
        }

        return $warnings;
    }

    /** Resumen corto para enseñarle al cliente que algo se hizo. */
    public function summarize(ReviewResult $result): string
    {
        if (! $result->hasFindings()) {
            return 'Revisamos tu texto y está listo para el documento.';
        }

        $count = count($result->issues);

        return $count > 0
            ? Str::plural('Corregimos '.$count.' detalle', $count).' de redacción antes de armar tu documento.'
            : 'Revisamos tu texto y te dejamos algunas sugerencias.';
    }
}
