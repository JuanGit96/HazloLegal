<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Documents\Services\SignatureService;
use App\Domain\Orders\Models\Order;
use Illuminate\Console\Command;

class SignatureDemo extends Command
{
    protected $signature = 'hl:signature-demo {reference?}';

    protected $description = 'Crea una petición de firma y muestra su enlace';

    public function handle(SignatureService $signatures): int
    {
        $order = $this->argument('reference')
            ? Order::query()->where('reference', $this->argument('reference'))->firstOrFail()
            : Order::query()->firstOrFail();

        $request = $signatures->request($order);

        $this->info($request->url());

        return self::SUCCESS;
    }
}
