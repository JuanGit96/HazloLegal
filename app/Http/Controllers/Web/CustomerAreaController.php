<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Orders\Mail\CustomerAccessMail;
use App\Domain\Orders\Models\Order;
use App\Domain\Seo\Services\SeoManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

/**
 * El área del cliente, sin contraseña.
 *
 * Aquí no hay cuentas que crear. El cliente escribe su correo y recibe un
 * enlace firmado que abre sus trámites; ese correo es el mismo al que le
 * llegaron los documentos, así que ya demostró que es suyo.
 *
 * Una contraseña añadiría fricción justo donde no hay nada que ganar: no
 * guardamos medios de pago, el valor es poder volver a descargar un PDF, y
 * cada contraseña es además una cosa más que se puede filtrar.
 */
class CustomerAreaController
{
    public function __construct(private readonly SeoManager $seo) {}

    public function create(): View
    {
        return view('account.request', [
            'meta' => $this->seo->page(
                title: 'Mis trámites | HazloLegal',
                description: 'Entra con tu correo y vuelve a descargar los documentos que has generado. Sin contraseña.',
                canonical: route('account.request'),
                crumbs: [
                    ['name' => 'Inicio', 'url' => url('/')],
                    ['name' => 'Mis trámites', 'url' => route('account.request')],
                ],
            ),
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', app()->isProduction() ? 'email:rfc,dns' : 'email:rfc', 'max:160'],
        ], [
            'email.required' => 'Escribe el correo con el que pediste tus trámites.',
        ]);

        $email = mb_strtolower(trim($validated['email']));

        $tiene = Order::query()->where('customer_email', $email)->exists();

        // Se responde igual exista o no: decir «ese correo no tiene pedidos»
        // permitiría averiguar quién nos compró y quién no.
        if ($tiene) {
            Mail::to($email)->send(new CustomerAccessMail($email, $this->linkFor($email)));
        }

        return back()->with('accountStatus', $email);
    }

    public function show(Request $request, string $email): View
    {
        // La URL va firmada: sin la firma de Laravel no se puede fabricar.
        abort_unless($request->hasValidSignature(), 403, 'Este enlace ya venció. Pide uno nuevo.');

        $orders = Order::query()
            ->with(['document', 'addons'])
            ->where('customer_email', $email)
            ->latest()
            ->get();

        return view('account.index', [
            'meta' => $this->seo->page(
                title: 'Mis trámites | HazloLegal',
                description: 'Tus documentos y pedidos.',
                canonical: route('account.request'),
                noindex: true,
            ),
            'email' => $email,
            'orders' => $orders,
        ]);
    }

    /** Media hora: suficiente para abrir el correo, poco para que quede vivo. */
    private function linkFor(string $email): string
    {
        return URL::temporarySignedRoute('account.show', now()->addMinutes(30), ['email' => $email]);
    }
}
