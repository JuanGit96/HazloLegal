<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Documents\Models\SignatureRequest;
use App\Domain\Documents\Services\SignatureService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * La pantalla de firma del celular.
 *
 * No lleva la cabecera ni el pie del sitio a propósito: quien abre esto ya
 * decidió, llega desde un QR y lo único que tiene que hacer es firmar. Cada
 * elemento de navegación aquí es una oportunidad de abandonar.
 */
class SignatureController
{
    public function __construct(private readonly SignatureService $signatures) {}

    public function show(string $token, Request $request): View
    {
        $signature = $this->resolve($token);

        $this->signatures->markOpened($signature);

        return view('signature.show', [
            'signature' => $signature,
            'order' => $signature->order()->with('document')->firstOrFail(),
        ]);
    }

    public function store(string $token, Request $request): RedirectResponse
    {
        $signature = $this->resolve($token);

        abort_if($signature->isSigned(), 410);

        $validated = $request->validate([
            'signature' => ['required', 'string', 'min:60', 'max:200000'],
            'confirms' => ['accepted'],
        ], [
            'signature.required' => 'Traza tu firma antes de continuar.',
            'signature.min' => 'La firma quedó demasiado corta. Inténtalo otra vez.',
            'confirms.accepted' => 'Confirma que eres tú quien firma.',
        ]);

        $this->signatures->sign(
            $signature,
            $validated['signature'],
            (string) $request->ip(),
            (string) $request->userAgent(),
        );

        return redirect()->route('signature.done', $token);
    }

    public function done(string $token): View
    {
        $signature = SignatureRequest::query()->where('token', $token)->firstOrFail();

        abort_unless($signature->isSigned(), 404);

        return view('signature.done', [
            'signature' => $signature,
            'order' => $signature->order()->with('document')->firstOrFail(),
        ]);
    }

    private function resolve(string $token): SignatureRequest
    {
        $signature = SignatureRequest::query()->where('token', $token)->firstOrFail();

        abort_if($signature->hasExpired(), 410, 'Este enlace de firma ya venció.');

        return $signature;
    }
}
