<?php

declare(strict_types=1);

use App\Http\Controllers\Web\BlogController;
use App\Http\Controllers\Web\CalculatorController;
use App\Http\Controllers\Web\CheckoutController;
use App\Http\Controllers\Web\ContactController;
use App\Http\Controllers\Web\CookieConsentController;
use App\Http\Controllers\Web\CustomerAreaController;
use App\Http\Controllers\Web\DocumentController;
use App\Http\Controllers\Web\FreeToolsController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\LawyerDirectoryController;
use App\Http\Controllers\Web\LawyerLandingController;
use App\Http\Controllers\Web\LegalCaseController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\SignatureController;
use App\Http\Controllers\Web\SitemapController;
use App\Http\Controllers\Web\SubscriptionCheckoutController;
use App\Http\Controllers\Web\WompiController;
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
    Route::get('/tramites', [CheckoutController::class, 'documents'])->name('documents');

    // Un solo eje: el trámite. Los adicionales viajan como parámetros, no
    // como segmentos de ruta, porque son una selección, no una jerarquía.
    Route::get('/{document:slug}', [CheckoutController::class, 'configure'])->name('configure');
    Route::post('/{document:slug}', [CheckoutController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('store');
});

Route::get('/pago/{order:reference}', [CheckoutController::class, 'payment'])->name('checkout.payment');
Route::post('/pago/{order:reference}', [CheckoutController::class, 'pay'])
    ->middleware('throttle:20,1')
    ->name('checkout.pay');
Route::get('/pedido/{order:reference}', [CheckoutController::class, 'confirmation'])->name('checkout.confirmation');
Route::post('/pedido/{order:reference}/agregar', [CheckoutController::class, 'addAddon'])
    ->middleware('throttle:20,1')
    ->name('checkout.add-addon');
Route::get('/pedido/{order:reference}/descargar/{kind}', [CheckoutController::class, 'download'])
    ->whereIn('kind', ['documento', 'instructivo'])
    ->middleware('throttle:30,1')
    ->name('checkout.download');

// -------------------------------------------------- Área del cliente
// Sin contraseña: el cliente pide un enlace firmado a su correo, que es el
// mismo al que le llegaron los documentos.
Route::get('/mis-tramites', [CustomerAreaController::class, 'create'])
    ->name('account.request');
Route::post('/mis-tramites', [CustomerAreaController::class, 'send'])
    ->middleware('throttle:6,1')->name('account.request.send');
Route::get('/mis-tramites/{email}', [CustomerAreaController::class, 'show'])
    ->middleware('throttle:30,1')->name('account.show');

// -------------------------------------------------- Suscripción del abogado
// Detrás del login del portal: paga quien ya fue verificado.
Route::get('/portal/suscripcion/pagar', [SubscriptionCheckoutController::class, 'show'])
    ->middleware(['web', 'auth'])
    ->name('subscription.checkout');
// El número de la tarjeta no llega aquí: el navegador lo cambia por un token
// contra Wompi y esto solo recibe el token.
Route::post('/portal/suscripcion/tarjeta', [SubscriptionCheckoutController::class, 'registerCard'])
    ->middleware(['web', 'auth', 'throttle:10,1'])
    ->name('subscription.card');

// -------------------------------------------------- Pasarela de pagos
// El webhook queda fuera de la protección CSRF porque lo llama Wompi, no un
// navegador: su autenticidad se comprueba con la firma del propio evento.
Route::post('/wompi/eventos', [WompiController::class, 'webhook'])
    ->name('wompi.webhook');
Route::get('/wompi/retorno/{reference}', [WompiController::class, 'returned'])
    ->name('wompi.return');

// -------------------------------------------------- Firma electrónica
// El token del enlace es la credencial: quien abre el QR es quien firma.
Route::get('/firmar/{token}', [SignatureController::class, 'show'])
    ->middleware('throttle:60,1')->name('signature.show');
Route::post('/firmar/{token}', [SignatureController::class, 'store'])
    ->middleware('throttle:20,1')->name('signature.store');
Route::get('/firmar/{token}/listo', [SignatureController::class, 'done'])
    ->name('signature.done');

// -------------------------------------------------- Casos complejos
Route::get('/caso-complejo', [LegalCaseController::class, 'create'])->name('cases.create');
Route::post('/caso-complejo', [LegalCaseController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('cases.store');
Route::get('/caso-complejo/recibido/{case:reference}', [LegalCaseController::class, 'received'])->name('cases.received');

// El cliente elige a su abogado entre los que se postularon. La llave del
// enlace es la credencial: la referencia es correlativa.
Route::get('/caso-complejo/{case:reference}/elegir', [LegalCaseController::class, 'choose'])
    ->middleware('throttle:60,1')->name('cases.choose');
Route::post('/caso-complejo/{case:reference}/elegir', [LegalCaseController::class, 'pick'])
    ->middleware('throttle:20,1')->name('cases.pick');

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

// La política de cookies es también el panel de preferencias: el visitante
// cambia o revoca su decisión en la misma página donde se le explica qué
// hace cada cookie, y sin JavaScript.
Route::get('/politica-de-cookies', [CookieConsentController::class, 'policy'])->name('pages.cookies');
Route::post('/politica-de-cookies', [CookieConsentController::class, 'store'])
    ->middleware('throttle:30,1')->name('cookies.store');

Route::get('/contacto', [ContactController::class, 'create'])->name('pages.contact');
Route::post('/contacto', [ContactController::class, 'store'])
    ->middleware('throttle:10,1')->name('pages.contact.store');

// Derecho de supresión (Ley 1581 de 2012). Tiene página propia y no una
// dirección de correo escondida en la política: si el derecho existe, tiene
// que poder ejercerse sin buscarlo.
Route::get('/eliminar-mis-datos', [ContactController::class, 'deletion'])->name('pages.data-deletion');
Route::post('/eliminar-mis-datos', [ContactController::class, 'storeDeletion'])
    ->middleware('throttle:10,1')->name('pages.data-deletion.store');

// -------------------------------------------------- SEO técnico
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

/*
|--------------------------------------------------------------------------
| Herramientas gratuitas
|--------------------------------------------------------------------------
| El imán del embudo. Documentos sin costo y calculadoras que atraen tráfico
| de alta intención; el dinero entra por los adicionales, por la suscripción
| de abogados y por el lead que el usuario autoriza compartir.
*/
Route::get('/gratis', [FreeToolsController::class, 'index'])->name('tools.index');
Route::get('/gratis/calculadoras/{calculator:slug}', [CalculatorController::class, 'show'])->name('tools.calculator');
Route::post('/gratis/calculadoras/{calculator:slug}', [CalculatorController::class, 'calculate'])
    ->middleware('throttle:60,1')->name('tools.calculator.run');

/*
|--------------------------------------------------------------------------
| Abogados: captación y directorio
|--------------------------------------------------------------------------
*/
Route::get('/abogados', [LawyerDirectoryController::class, 'index'])->name('lawyers.index');
Route::get('/abogados/registro', [LawyerLandingController::class, 'index'])->name('lawyers.join');
// El enlace que se le pasa a un abogado invitado. Va antes de la ficha
// pública para que «invitacion» no se confunda con el slug de un abogado.
Route::get('/abogados/invitacion/{code}', [LawyerLandingController::class, 'invitation'])
    ->middleware('throttle:30,1')->name('lawyers.invitation');
Route::post('/abogados/registro', [LawyerLandingController::class, 'store'])
    ->middleware('throttle:10,1')->name('lawyers.join.store');
Route::get('/abogados/{lawyer:slug}/contacto', [LawyerDirectoryController::class, 'contact'])
    ->middleware('throttle:60,1')->name('lawyers.contact');
Route::get('/abogados/{lawyer:slug}', [LawyerDirectoryController::class, 'show'])->name('lawyers.show');
