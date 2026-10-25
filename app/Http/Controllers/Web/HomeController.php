<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Catalog\Services\CatalogService;
use App\Domain\Catalog\Services\PricingService;
use App\Domain\Content\Models\FaqEntry;
use App\Domain\Content\Models\Testimonial;
use App\Domain\Content\Services\BlogService;
use App\Domain\Lawyers\Services\LawyerDirectory;
use App\Domain\Seo\Services\SeoManager;
use Illuminate\Contracts\View\View;

class HomeController
{
    public function __invoke(
        CatalogService $catalog,
        PricingService $pricing,
        BlogService $blog,
        LawyerDirectory $lawyers,
        SeoManager $seo,
    ): View {
        $documents = $catalog->documents();
        $faqs = FaqEntry::query()->inGroup('home')->get();

        $meta = $seo->withFaqSchema(
            $seo->home($documents),
            $faqs->map(fn (FaqEntry $f) => ['question' => $f->question, 'answer' => $f->answer])->all(),
        );

        return view('home.index', [
            'meta' => $meta,
            'documents' => $documents,
            'groupedDocuments' => $catalog->groupedByArea(),
            'plans' => $catalog->publicPlans(),
            'entryPrice' => $pricing->entryPrice(),
            'pricing' => $pricing,
            'testimonials' => Testimonial::query()->onHome()->limit(8)->get(),
            'faqs' => $faqs,
            'posts' => $blog->latest(3),
            'team' => $lawyers->publicTeam(4),
        ]);
    }
}
