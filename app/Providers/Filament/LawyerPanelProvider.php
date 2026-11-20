<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * El portal del abogado suscrito.
 *
 * Es un panel aparte y no una sección del de administración a propósito: lo
 * que separa a los dos públicos no es un menú escondido, es el límite de lo
 * que cada consulta puede devolver. Aquí dentro todo está acotado al abogado
 * que entró, y nada de lo que ve incluye pedidos, ingresos ni clientes de
 * otros.
 */
class LawyerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('lawyer')
            ->path('portal')
            ->login()
            ->brandName('HazloLegal · Abogados')
            ->brandLogo(fn () => view('filament.brand', ['sufijo' => 'Abogados']))
            ->favicon(asset('img/favicon.svg'))
            ->colors([
                'primary' => Color::Blue,
                'danger' => Color::Rose,
                'warning' => Color::Amber,
                'success' => Color::Emerald,
                'info' => Color::Sky,
            ])
            ->navigationGroups([
                NavigationGroup::make('Mi trabajo')
                    ->icon(Heroicon::OutlinedBriefcase),
                NavigationGroup::make('Mi presencia')
                    ->icon(Heroicon::OutlinedUserCircle),
            ])
            ->discoverResources(in: app_path('Filament/Lawyer/Resources'), for: 'App\Filament\Lawyer\Resources')
            ->discoverPages(in: app_path('Filament/Lawyer/Pages'), for: 'App\Filament\Lawyer\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Lawyer/Widgets'), for: 'App\Filament\Lawyer\Widgets')
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class]);
    }
}
