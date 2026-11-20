<?php

declare(strict_types=1);

namespace App\Domain\Shared\Concerns;

trait RoundsPrices
{
    /**
     * Redondea a una cifra que parezca un precio de verdad.
     *
     * Un precio tachado de $70.471 delata que lo calculó una máquina, y un
     * precio anterior que nadie cobró nunca no debería mostrarse. Lo correcto
     * es que el equipo escriba el precio real en «Precio tachado»; el
     * porcentaje es un atajo, y esto evita que el atajo quede en evidencia.
     */
    protected function roundToPricePoint(float $amount): int
    {
        if ($amount <= 0) {
            return 0;
        }

        // Por debajo de $10.000 se redondea al millar; por encima, al millar
        // terminado en 900, que es como se escriben los precios en Colombia.
        if ($amount < 10_000) {
            return (int) (round($amount / 1_000) * 1_000);
        }

        return (int) (round(($amount - 900) / 1_000) * 1_000 + 900);
    }
}
