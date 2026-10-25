<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Content\Models\Post;
use App\Domain\Content\Models\PostCategory;
use App\Domain\Content\Services\BlogService;
use App\Domain\Seo\Services\SeoManager;
use App\Domain\Shared\Models\Lead;
use App\Http\Requests\StoreLeadRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class BlogController
{
    public function __construct(
        private readonly BlogService $blog,
        private readonly SeoManager $seo,
    ) {}

    public function index(): View
    {
        return view('blog.index', [
            'meta' => $this->seo->blogIndex(),
            'posts' => $this->blog->paginate(),
            'featured' => $this->blog->featured(2),
            'categories' => $this->blog->categories(),
            'category' => null,
        ]);
    }

    public function category(PostCategory $category): View
    {
        return view('blog.index', [
            'meta' => $this->seo->blogIndex($category),
            'posts' => $this->blog->paginate(category: $category),
            'featured' => collect(),
            'categories' => $this->blog->categories(),
            'category' => $category,
        ]);
    }

    public function show(Post $post): View
    {
        $this->blog->registerView($post);

        return view('blog.show', [
            'meta' => $this->seo->post($post),
            'post' => $post,
            'related' => $this->blog->related($post),
            'categories' => $this->blog->categories(),
        ]);
    }

    /** Imán de leads: la plantilla gratis a cambio del correo. */
    public function leadMagnet(StoreLeadRequest $request, Post $post): RedirectResponse
    {
        Lead::query()->updateOrCreate(
            ['email' => mb_strtolower($request->string('email')->toString()), 'source' => 'lead_magnet'],
            [
                'name' => $request->input('name'),
                'post_id' => $post->id,
                'legal_document_id' => $post->related_document_id,
                'consented_at' => now(),
                'ip_address' => $request->ip(),
                'is_subscribed' => true,
            ],
        );

        return back()->with('leadStatus', 'Listo. Te enviamos la plantilla al correo en los próximos minutos.');
    }
}
