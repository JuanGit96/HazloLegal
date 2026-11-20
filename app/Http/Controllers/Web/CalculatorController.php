<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Seo\Services\SeoManager;
use App\Domain\Tools\Models\Calculator;
use App\Domain\Tools\Services\LaborCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CalculatorController
{
    public function __construct(
        private readonly SeoManager $seo,
        private readonly LaborCalculator $calculator,
    ) {}

    public function show(Calculator $calculator): View
    {
        abort_unless($calculator->is_active, 404);

        $calculator->incrementQuietly('uses_count');

        return view('tools.calculator', [
            'meta' => $this->meta($calculator),
            'calculator' => $calculator,
            'result' => null,
            'related' => Calculator::query()->active()->whereKeyNot($calculator->id)->limit(3)->get(),
        ]);
    }

    public function calculate(Request $request, Calculator $calculator): View
    {
        abort_unless($calculator->is_active, 404);

        $result = match ($calculator->key) {
            'liquidacion' => $this->liquidation($request, $calculator),
            'indemnizacion' => $this->severance($request, $calculator),
            'horas_extras' => $this->overtime($request),
            'intereses_mora' => $this->lateInterest($request, $calculator),
            default => null,
        };

        return view('tools.calculator', [
            'meta' => $this->meta($calculator),
            'calculator' => $calculator,
            'result' => $result,
            'related' => Calculator::query()->active()->whereKeyNot($calculator->id)->limit(3)->get(),
        ]);
    }

    private function liquidation(Request $request, Calculator $calculator): array
    {
        $data = $request->validate([
            'salary' => ['required', 'integer', 'min:1'],
            'days' => ['required', 'integer', 'min:1', 'max:20000'],
            'transport' => ['nullable', 'boolean'],
            'pending_vacation_days' => ['nullable', 'integer', 'min:0', 'max:120'],
        ]);

        return $this->calculator->liquidation(
            monthlySalary: (int) $data['salary'],
            daysWorked: (int) $data['days'],
            includesTransportAllowance: (bool) ($data['transport'] ?? false),
            transportAllowance: (int) $calculator->param('transport_allowance', 0),
            daysOfPendingVacation: (int) ($data['pending_vacation_days'] ?? 0),
        );
    }

    private function severance(Request $request, Calculator $calculator): array
    {
        $data = $request->validate([
            'salary' => ['required', 'integer', 'min:1'],
            'days' => ['required', 'integer', 'min:1', 'max:20000'],
            'contract_type' => ['required', 'in:indefinite,fixed'],
            'remaining_days' => ['nullable', 'integer', 'min:0'],
        ]);

        return $this->calculator->severancePay(
            monthlySalary: (int) $data['salary'],
            daysWorked: (int) $data['days'],
            contractType: $data['contract_type'],
            remainingDays: (int) ($data['remaining_days'] ?? 0),
        );
    }

    private function overtime(Request $request): array
    {
        $data = $request->validate([
            'salary' => ['required', 'integer', 'min:1'],
            'extra_diurna' => ['nullable', 'integer', 'min:0', 'max:400'],
            'extra_nocturna' => ['nullable', 'integer', 'min:0', 'max:400'],
            'recargo_nocturno' => ['nullable', 'integer', 'min:0', 'max:400'],
            'dominical' => ['nullable', 'integer', 'min:0', 'max:400'],
        ]);

        return $this->calculator->overtime((int) $data['salary'], $data);
    }

    private function lateInterest(Request $request, Calculator $calculator): array
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'days' => ['required', 'integer', 'min:1', 'max:3650'],
        ]);

        return $this->calculator->lateInterest(
            amount: (int) $data['amount'],
            days: (int) $data['days'],
            annualRate: (float) $calculator->param('max_annual_rate', 25.5),
        );
    }

    private function meta(Calculator $calculator): \App\Domain\Seo\Data\SeoMeta
    {
        $meta = $this->seo->page(
            title: $calculator->metaTitle(),
            description: $calculator->seo_description ?: $calculator->intro,
            canonical: $calculator->url(),
            crumbs: [
                ['name' => 'Inicio', 'url' => url('/')],
                ['name' => 'Herramientas gratis', 'url' => route('tools.index')],
                ['name' => $calculator->name, 'url' => $calculator->url()],
            ],
        );

        return $this->seo->withFaqSchema($meta, $calculator->faqs ?? []);
    }
}
