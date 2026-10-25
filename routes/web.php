<?php

declare(strict_types=1);

use App\Http\Controllers\Web\BlogController;
use App\Http\Controllers\Web\CheckoutController;
use App\Http\Controllers\Web\DocumentController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\LegalCaseController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sitio público
|--------------------------------------------------------------------------
| URLs en español y sin parámetros técnicos: son las que indexa Google y
| las que el cliente lee antes de hacer clic.
*/

Route::get('/', HomeController::class)->name('home');

// -------------------------------------------------- Catálogo y landings SEO
Route::get('/documentos', [DocumentController::class, 'index'])->name('documents.index');
Route::get('/tramites/{area}', [DocumentController::class, 'area'])->name('documents.area');
Route::get('/documentos/{document:slug}', [DocumentController::class, 'show'])->name('documents.show');

// -------------------------------------------------- Embudo de compra
Route::prefix('contratar')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'plans'])->name('plans');
    Route::get('/{plan:key}', [CheckoutController::class, 'documents'])->name('documents');

    // Sin scoped bindings: el documento no es hijo del plan, son dos ejes
    // independientes (cualquier plan sirve para cualquier documento).
    Route::get('/{plan:key}/{document:slug}', [CheckoutController::class, 'configure'])
        ->withoutScopedBindings()
        ->name('configure');
    Route::post('/{plan:key}/{document:slug}', [CheckoutController::class, 'store'])
        ->withoutScopedBindings()
        ->middleware('throttle:20,1')
        ->name('store');
});

Route::get('/pago/{order:reference}', [CheckoutController::class, 'payment'])->name('checkout.payment');
Route::post('/pago/{order:reference}', [CheckoutController::class, 'pay'])
    ->middleware('throttle:20,1')
    ->name('checkout.pay');
Route::get('/pedido/{order:reference}', [CheckoutController::class, 'confirmation'])->name('checkout.confirmation');

// -------------------------------------------------- Casos complejos
Route::get('/caso-complejo', [LegalCaseController::class, 'create'])->name('cases.create');
Route::post('/caso-complejo', [LegalCaseController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('cases.store');
Route::get('/caso-complejo/recibido/{case:reference}', [LegalCaseController::class, 'received'])->name('cases.received');

// Respuesta del abogado desde el correo. Sin login: el token es la credencial.
Route::get('/casos/oferta/{token}/{decision}', [LegalCaseController::class, 'respond'])
    ->whereIn('decision', ['accept', 'decline'])
    ->middleware('throttle:60,1')
    ->name('cases.offer.respond');
Route::post('/casos/oferta/{token}/rechazar', [LegalCaseController::class, 'decline'])
    ->middleware('throttle:60,1')
    ->name('cases.offer.decline');

// -------------------------------------------------- Blog
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/categoria/{category:slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');
Route::post('/blog/{post:slug}/plantilla', [BlogController::class, 'leadMagnet'])
    ->middleware('throttle:10,1')
    ->name('blog.lead-magnet');

// -------------------------------------------------- Páginas de confianza y legales
Route::get('/garantia', [PageController::class, 'guarantee'])->name('pages.guarantee');
Route::post('/garantia/reclamar', [PageController::class, 'storeClaim'])
    ->middleware('throttle:10,1')
    ->name('pages.guarantee.claim');
Route::get('/como-funciona', [PageController::class, 'howItWorks'])->name('pages.how-it-works');
Route::get('/nosotros', [PageController::class, 'about'])->name('pages.about');
Route::get('/terminos-y-condiciones', [PageController::class, 'terms'])->name('pages.terms');
Route::get('/tratamiento-de-datos-personales', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/validez-de-la-firma-electronica', [PageController::class, 'signature'])->name('pages.signature');

// -------------------------------------------------- SEO técnico
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
