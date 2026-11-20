<?php

declare(strict_types=1);

namespace App\Domain\Shared\Services;

use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\Shared\Models\Lead;

/**
 * Captura de leads con consentimiento explícito.
 *
 * El documento gratuito no es caridad: es el canal por el que entra el dato
 * que sostiene la suscripción de abogados. Pero el dato solo sirve si está
 * bien obtenido — un lead sin autorización no se puede compartir, y un
 * registro mal llevado expone a la empresa ante la SIC.
 *
 * Por eso aquí nunca se marca un consentimiento que el usuario no haya dado,
 * y cada autorización queda con su fecha, su IP y la versión de la política.
 */
final class LeadCaptureService
{
    public function capture(array $data): Lead
    {
        $email = mb_strtolower(trim((string) $data['email']));
        $source = $data['source'] ?? 'lead_magnet';

        $consentMarketing = (bool) ($data['consent_marketing'] ?? false);
        $consentShare = (bool) ($data['consent_share_with_lawyers'] ?? false);

        /** @var Lead $lead */
        $lead = Lead::query()->updateOrCreate(
            ['email' => $email, 'source' => $source],
            [
                'name' => $data['name'] ?? null,
                'phone' => $data['phone'] ?? null,
                'city' => $data['city'] ?? null,
                'practice_area' => isset($data['practice_area'])
                    ? PracticeArea::tryFrom((string) $data['practice_area'])
                    : null,
                'case_summary' => $data['case_summary'] ?? null,
                'order_id' => $data['order_id'] ?? null,
                'post_id' => $data['post_id'] ?? null,
                'calculator_id' => $data['calculator_id'] ?? null,
                'legal_document_id' => $data['legal_document_id'] ?? null,

                'consented_at' => now(),
                'consent_marketing' => $consentMarketing,
                'consent_share_with_lawyers' => $consentShare,
                'consent_policy_version' => config('hazlolegal.privacy.policy_version', '1.0'),
                'consent_revoked_at' => null,

                'ip_address' => $data['ip_address'] ?? null,
                'is_subscribed' => $consentMarketing,
                'last_activity_at' => now(),
                'quality' => $this->quality($data),
            ],
        );

        return $lead;
    }

    /**
     * Temperatura del lead.
     *
     * No es un dato vanidoso: decide a qué abogado se le muestra primero y
     * cuánto vale la suscripción. Quien describió un caso concreto y autorizó
     * que lo contacten vale mucho más que quien solo dejó un correo.
     */
    private function quality(array $data): string
    {
        $hasCase = filled($data['case_summary'] ?? null);
        $shares = (bool) ($data['consent_share_with_lawyers'] ?? false);
        $hasPhone = filled($data['phone'] ?? null);

        return match (true) {
            $hasCase && $shares && $hasPhone => 'hot',
            $hasCase || $shares => 'warm',
            default => 'cold',
        };
    }

    /** Revocación: el titular puede retirar la autorización cuando quiera. */
    public function revoke(Lead $lead): Lead
    {
        $lead->update([
            'consent_marketing' => false,
            'consent_share_with_lawyers' => false,
            'consent_revoked_at' => now(),
            'is_subscribed' => false,
        ]);

        return $lead->refresh();
    }
}
