<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Payments\Services\WompiGateway;
use Illuminate\Console\Command;

class WompiCheck extends Command
{
    protected $signature = 'hl:wompi-check {transaction}';

    protected $description = 'Consulta una transacción en Wompi y muestra su estado';

    public function handle(WompiGateway $wompi): int
    {
        $this->line('API: '.$wompi->apiUrl());

        $data = $wompi->fetch($this->argument('transaction'));

        if (! $data) {
            $this->error('No se pudo leer la transacción. Revisa storage/logs.');

            return self::FAILURE;
        }

        foreach (['reference', 'status', 'amount_in_cents', 'payment_method_type'] as $key) {
            $this->line(sprintf('%-20s %s', $key, $data[$key] ?? '—'));
        }

        return self::SUCCESS;
    }
}
