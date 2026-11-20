<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Catalog\Models\ServiceAddon;
use App\Domain\Catalog\Services\CatalogService;
use App\Domain\Catalog\Services\PricingService;
use App\Domain\Content\Models\FaqEntry;
use App\Domain\Content\Models\Testimonial;
use App\Domain\Content\Services\BlogService;
use App\Domain\Lawyers\Services\LawyerDirectory;
use App\Domain\Seo\Services\SeoManager;
use App\Domain\Settings\Services\SettingsService;
use App\Domain\Tools\Models\Calculator;
use Illuminate\Contracts\View\View;

class HomeController
{
    public function __invoke(
        CatalogService $catalog,
        PricingService $pricing,
        BlogService $blog,
        LawyerDirectory $lawyers,
        SeoManager $seo,
        SettingsService $settings,
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
            'freeDocuments' => $catalog->freeDocuments(4),
            'featured' => $catalog->featuredDocuments(6),
            'addons' => ServiceAddon::query()->active()->get(),
            'calculators' => Calculator::query()->where('is_active', true)->orderBy('sort_order')->limit(4)->get(),
            'entryPrice' => $pricing->entryPrice(),
            'pricing' => $pricing,
            'settings' => $settings,
            'testimonials' => Testimonial::query()->onHome()->limit(8)->get(),
            'faqs' => $faqs,
            'posts' => $blog->latest(3),
            'team' => $lawyers->publicTeam(4),
        ]);
    }
}
