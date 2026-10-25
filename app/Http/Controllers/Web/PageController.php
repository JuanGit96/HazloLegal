<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Catalog\Services\CatalogService;
use App\Domain\Catalog\Services\PricingService;
use App\Domain\Content\Models\FaqEntry;
use App\Domain\Lawyers\Services\LawyerDirectory;
use App\Domain\Orders\Models\Order;
use App\Domain\Seo\Services\SeoManager;
use App\Domain\Shared\Enums\ClaimReason;
use App\Domain\Shared\Models\GuaranteeClaim;
use App\Domain\Shared\Services\ReferenceGenerator;
use App\Http\Requests\StoreClaimRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PageController
{
    public function __construct(private readonly SeoManager $seo) {}

    public function guarantee(): View
    {
        $faqs = FaqEntry::query()->inGroup('guarantee')->get();

        $meta = $this->seo->withFaqSchema(
            $this->seo->page(
                title: 'Certificado de Garantía Hazlo Legal: corregimos o devolvemos tu dinero',
                description: 'Si tu documento tiene errores de redacción o datos, lo corregimos en 24 horas sin costo. Si una entidad lo rechaza por defecto de forma nuestro, te devolvemos el 100 %. Por escrito.',
                canonical: route('pages.guarantee'),
                crumbs: [
                    ['name' => 'Inicio', 'url' => url('/')],
                    ['name' => 'Certificado de Garantía', 'url' => route('pages.guarantee')],
                ],
            ),
            $faqs->map(fn (FaqEntry $f) => ['question' => $f->question, 'answer' => $f->answer])->all(),
        );

        return view('pages.guarantee', [
            'meta' => $meta,
            'faqs' => $faqs,
            'reasons' => ClaimReason::cases(),
            'guarantee' => config('hazlolegal.guarantee'),
        ]);
    }

    public function storeClaim(StoreClaimRequest $request, ReferenceGenerator $references): RedirectResponse
    {
        $reference = $request->string('order_reference')->trim()->toString();

        GuaranteeClaim::query()->create([
            'reference' => $references->for(GuaranteeClaim::class, 'GAR'),
            'order_id' => Order::query()->where('reference', $reference)->value('id'),
            'order_reference' => $reference,
            'client_name' => $request->string('client_name')->toString(),
            'client_email' => mb_strtolower($request->string('client_email')->toString()),
            'reason' => $request->enum('reason', ClaimReason::class),
            'description' => $request->string('description')->toString(),
        ]);

        return back()->with('claimStatus', 'Recibimos tu solicitud. Un abogado la revisa y te responde dentro de las próximas 24 horas hábiles.');
    }

    public function howItWorks(CatalogService $catalog, PricingService $pricing): View
    {
        return view('pages.how-it-works', [
            'meta' => $this->seo->page(
                title: 'Cómo funciona Hazlo Legal: de tu problema al documento en 4 pasos',
                description: 'Eliges el trámite, cuentas tu caso, un abogado titulado lo audita y recibes el PDF listo para radicar. Sin oficinas, sin honorarios sorpresa.',
                canonical: route('pages.how-it-works'),
                crumbs: [
                    ['name' => 'Inicio', 'url' => url('/')],
                    ['name' => 'Cómo funciona', 'url' => route('pages.how-it-works')],
                ],
            ),
            'plans' => $catalog->plans(),
            'pricing' => $pricing,
        ]);
    }

    public function about(LawyerDirectory $directory): View
    {
        return view('pages.about', [
            'meta' => $this->seo->page(
                title: 'Quiénes somos | Abogados titulados detrás de Hazlo Legal',
                description: 'Somos un equipo de abogados colombianos con tarjeta profesional que automatizó lo repetitivo para cobrar menos y responder más rápido.',
                canonical: route('pages.about'),
                crumbs: [
                    ['name' => 'Inicio', 'url' => url('/')],
                    ['name' => 'Nosotros', 'url' => route('pages.about')],
                ],
            ),
            'team' => $directory->publicTeam(12),
        ]);
    }

    public function terms(): View
    {
        return view('pages.terms', [
            'meta' => $this->seo->page(
                title: 'Términos y condiciones | Hazlo Legal',
                description: 'Condiciones de uso del servicio de documentos y acompañamiento legal de Hazlo Legal.',
                canonical: route('pages.terms'),
            ),
        ]);
    }

    public function privacy(): View
    {
        return view('pages.privacy', [
            'meta' => $this->seo->page(
                title: 'Política de tratamiento de datos personales | Hazlo Legal',
                description: 'Cómo recolectamos, usamos y protegemos tus datos personales conforme a la Ley 1581 de 2012 y el Decreto 1377 de 2013.',
                canonical: route('pages.privacy'),
            ),
        ]);
    }

    public function signature(): View
    {
        $faqs = FaqEntry::query()->inGroup('signature')->get();

        $meta = $this->seo->withFaqSchema(
            $this->seo->page(
                title: '¿Es válida la firma electrónica en Colombia? Ley 527 de 1999 explicada',
                description: 'La firma electrónica con OTP y correo tiene plena validez probatoria en Colombia según la Ley 527 de 1999 y el Decreto 2364 de 2012. Te explicamos cuándo sirve y cuándo necesitas firma digital certificada.',
                canonical: route('pages.signature'),
                crumbs: [
                    ['name' => 'Inicio', 'url' => url('/')],
                    ['name' => 'Validez de la firma electrónica', 'url' => route('pages.signature')],
                ],
            ),
            $faqs->map(fn (FaqEntry $f) => ['question' => $f->question, 'answer' => $f->answer])->all(),
        );

        return view('pages.signature', ['meta' => $meta, 'faqs' => $faqs]);
    }
}
