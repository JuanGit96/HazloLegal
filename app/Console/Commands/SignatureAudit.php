<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Catalog\Models\ServiceAddon;
use Illuminate\Console\Command;

/**
 * Comprueba que el adicional de firma esté donde tiene sentido.
 *
 * Ofrecer firma en un trámite marcado como «no admite firma» es venderle al
 * cliente algo que la ficha le acaba de negar.
 */
class SignatureAudit extends Command
{
    protected $signature = 'hl:signature-audit';

    protected $description = 'Revisa dónde se ofrece el adicional de firma';

    public function handle(): int
    {
        $addon = ServiceAddon::query()->where('key', 'firma_digital')->first();

        if (! $addon) {
            $this->error('No existe el adicional firma_digital.');

            return self::FAILURE;
        }

        $this->line('Adicional: '.$addon->name.'  activo='.($addon->is_active ? 'sí' : 'NO')
            .'  precio='.$addon->default_price);
        $this->newLine();

        foreach (LegalDocument::query()->with('addons')->orderBy('sort_order')->get() as $document) {
            $pivot = $document->addons->firstWhere('id', $addon->id);
            $ofrece = $pivot !== null && (bool) $pivot->pivot->is_available;
            $admite = (bool) $document->supports_signature;

            $marca = match (true) {
                $ofrece && ! $admite => '  ⚠️  OFRECE FIRMA PERO LA FICHA DICE QUE NO LA ADMITE',
                ! $ofrece && $admite => '  ·  admite firma pero no se ofrece',
                default => '',
            };

            $this->line(sprintf(
                '%-44s ofrece=%-3s admite=%-3s%s',
                $document->slug,
                $ofrece ? 'sí' : 'no',
                $admite ? 'sí' : 'no',
                $marca,
            ));
        }

        return self::SUCCESS;
    }
}
