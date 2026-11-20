<?php

declare(strict_types=1);

namespace App\Domain\Tools\Services;

/**
 * Cálculos laborales colombianos.
 *
 * Los parámetros del año (salario mínimo, auxilio de transporte) vienen de
 * la configuración de cada calculadora en el panel: cambian cada enero y
 * nadie debería tener que tocar código para actualizarlos.
 *
 * Fórmulas del Código Sustantivo del Trabajo. Los resultados son estimados
 * y así se le dice al usuario: un caso real puede tener comisiones, pagos
 * disfrazados de auxilio o salario variable que cambian la base.
 */
final class LaborCalculator
{
    /** Liquidación completa de prestaciones sociales. */
    public function liquidation(
        int $monthlySalary,
        int $daysWorked,
        bool $includesTransportAllowance = true,
        int $transportAllowance = 0,
        int $daysOfPendingVacation = 0,
    ): array {
        $base = $monthlySalary + ($includesTransportAllowance ? $transportAllowance : 0);

        // Cesantías y prima se calculan sobre salario + auxilio; vacaciones no.
        $severance = (int) round($base * $daysWorked / 360);
        $severanceInterest = (int) round($severance * $daysWorked * 0.12 / 360);
        $bonus = (int) round($base * $this->daysInCurrentSemester($daysWorked) / 360);
        $vacation = (int) round($monthlySalary * $daysWorked / 720);
        $pendingVacation = (int) round($monthlySalary / 30 * $daysOfPendingVacation);

        $items = [
            'Cesantías' => $severance,
            'Intereses sobre cesantías' => $severanceInterest,
            'Prima de servicios' => $bonus,
            'Vacaciones' => $vacation + $pendingVacation,
        ];

        return [
            'items' => $items,
            'total' => array_sum($items),
            'base_used' => $base,
            'notes' => [
                'Las cesantías y la prima se calculan sobre salario más auxilio de transporte; las vacaciones solo sobre el salario.',
                'Si recibías comisiones o bonificaciones habituales, tu base real es mayor y esta cuenta se queda corta.',
            ],
        ];
    }

    /** Indemnización por despido sin justa causa (art. 64 CST). */
    public function severancePay(int $monthlySalary, int $daysWorked, string $contractType = 'indefinite', int $remainingDays = 0): array
    {
        $daily = $monthlySalary / 30;

        if ($contractType === 'fixed') {
            $amount = (int) round($daily * max(0, $remainingDays));

            return [
                'total' => $amount,
                'formula' => 'Contrato a término fijo: los salarios que faltaban para terminar el plazo.',
                'notes' => ['Nunca inferior a 15 días de salario.'],
            ];
        }

        $years = $daysWorked / 360;
        $isHighSalary = $monthlySalary >= $this->minimumWage() * 10;

        if ($isHighSalary) {
            $firstYear = 20;
            $perExtraYear = 15;
        } else {
            $firstYear = 30;
            $perExtraYear = 20;
        }

        $days = $years <= 1
            ? $firstYear
            : $firstYear + ($perExtraYear * ($years - 1));

        return [
            'total' => (int) round($daily * $days),
            'formula' => $isHighSalary
                ? 'Salario igual o superior a 10 SMLMV: 20 días por el primer año y 15 por cada año adicional.'
                : 'Salario inferior a 10 SMLMV: 30 días por el primer año y 20 por cada año adicional.',
            'notes' => ['Se liquida proporcionalmente por fracción de año.'],
        ];
    }

    /** Recargos por horas extras, nocturnas, dominicales y festivos. */
    public function overtime(int $monthlySalary, array $hours): array
    {
        $hourly = $monthlySalary / 240;

        $rates = [
            'extra_diurna' => ['label' => 'Hora extra diurna', 'rate' => 1.25],
            'extra_nocturna' => ['label' => 'Hora extra nocturna', 'rate' => 1.75],
            'recargo_nocturno' => ['label' => 'Recargo nocturno', 'rate' => 0.35],
            'dominical' => ['label' => 'Dominical o festivo', 'rate' => 1.75],
        ];

        $items = [];

        foreach ($rates as $key => $config) {
            $qty = (int) ($hours[$key] ?? 0);

            if ($qty > 0) {
                $items[$config['label']] = (int) round($hourly * $config['rate'] * $qty);
            }
        }

        return [
            'items' => $items,
            'total' => array_sum($items),
            'hourly_rate' => (int) round($hourly),
            'notes' => [
                'La hora ordinaria se calcula sobre una jornada de 240 horas al mes.',
                'Las horas extras también aumentan la base de tus prestaciones del período.',
            ],
        ];
    }

    /** Intereses moratorios al máximo legal permitido. */
    public function lateInterest(int $amount, int $days, float $annualRate): array
    {
        $daily = $annualRate / 100 / 365;
        $interest = (int) round($amount * $daily * $days);

        return [
            'interest' => $interest,
            'total' => $amount + $interest,
            'notes' => [
                'La tasa máxima legal la certifica la Superintendencia Financiera y cambia cada mes.',
                'Cobrar por encima de ese tope constituye usura.',
            ],
        ];
    }

    private function daysInCurrentSemester(int $daysWorked): int
    {
        return min($daysWorked, 180);
    }

    private function minimumWage(): int
    {
        return (int) config('hazlolegal.labor.minimum_wage', 1_623_500);
    }
}
