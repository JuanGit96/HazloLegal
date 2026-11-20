<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Content\Models\FaqEntry;
use App\Domain\Seo\Services\SeoManager;
use App\Domain\Shared\Enums\ContactKind;
use App\Domain\Shared\Services\ContactService;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\StoreDataDeletionRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ContactController
{
    public function __construct(
        private readonly ContactService $contact,
        private readonly SeoManager $seo,
    ) {}

    public function create(): View
    {
        $faqs = FaqEntry::query()->inGroup('home')->limit(4)->get();

        return view('pages.contact', [
            'meta' => $this->seo->withFaqSchema(
                $this->seo->page(
                    title: 'Contacto | HazloLegal',
                    description: 'Escríbenos y te respondemos en menos de 24 horas hábiles. También puedes hablarnos por WhatsApp.',
                    canonical: route('pages.contact'),
                    crumbs: [
                        ['name' => 'Inicio', 'url' => url('/')],
                        ['name' => 'Contacto', 'url' => route('pages.contact')],
                    ],
                ),
                $faqs->map(fn (FaqEntry $f) => ['question' => $f->question, 'answer' => $f->answer])->all(),
            ),
            'faqs' => $faqs,
        ]);
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        $message = $this->contact->receive($request->validated(), ContactKind::Contact);

        return back()->with('contactStatus', $message->reference);
    }

    public function deletion(): View
    {
        return view('pages.data-deletion', [
            'meta' => $this->seo->page(
                title: 'Eliminar mis datos personales | HazloLegal',
                description: 'Pide la eliminación de tus datos personales. Es tu derecho y lo atendemos en 15 días hábiles, como manda la Ley 1581 de 2012.',
                canonical: route('pages.data-deletion'),
                crumbs: [
                    ['name' => 'Inicio', 'url' => url('/')],
                    ['name' => 'Eliminar mis datos', 'url' => route('pages.data-deletion')],
                ],
                noindex: false,
            ),
        ]);
    }

    public function storeDeletion(StoreDataDeletionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['message'] = $data['message'] ?? 'Solicitud de eliminación de datos personales.';

        $message = $this->contact->receive($data, ContactKind::DataDeletion);

        return back()->with('deletionStatus', $message->reference);
    }
}
