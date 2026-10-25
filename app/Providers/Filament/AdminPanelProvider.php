<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Support\NavGroup;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
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

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Hazlo Legal')
            ->favicon(asset('img/favicon.svg'))
            ->colors([
                'primary' => Color::Blue,
                'danger' => Color::Rose,
                'warning' => Color::Amber,
                'success' => Color::Emerald,
                'info' => Color::Sky,
            ])
            ->navigationGroups([
                NavigationGroup::make(NavGroup::OPERATION)
                    ->icon(Heroicon::OutlinedInbox),
                NavigationGroup::make(NavGroup::CATALOG)
                    ->icon(Heroicon::OutlinedSquares2x2),
                NavigationGroup::make(NavGroup::NETWORK)
                    ->icon(Heroicon::OutlinedScale),
                NavigationGroup::make(NavGroup::CONTENT)
                    ->icon(Heroicon::OutlinedNewspaper),
                NavigationGroup::make(NavGroup::MARKETING)
                    ->icon(Heroicon::OutlinedMegaphone)
                    ->collapsed(),
                NavigationGroup::make(NavGroup::ROADMAP)
                    ->icon(Heroicon::OutlinedRocketLaunch)
                    ->collapsed(),
                NavigationGroup::make(NavGroup::SETTINGS)
                    ->icon(Heroicon::OutlinedCog6Tooth)
                    ->collapsed(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([Dashboard::class])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
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
