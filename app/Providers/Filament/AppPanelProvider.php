<?php

namespace App\Providers\Filament;

use App\Filament\Resources\ActivityDesignResource;
use App\Filament\Resources\ActivityDesignResource\Pages\ListActivityDesigns;
use App\Filament\Pages\ActivityDesign\SignatoryActivityDesigns;
use App\Filament\Resources\ActivityDesignResource\Pages\SubmittedActivityDesigns;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('/')
            ->navigationItems([
                NavigationItem::make('Back to V1')
                    ->url(config('app.v1_url'))
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->sort(-9),
            ])
            ->databaseTransactions()
            ->login(function () {
                if (Auth::check()) {
                    return redirect(route('filament.app.pages.dashboard'));
                }
                return redirect(config('app.v1_url')  . '/login');
            })
            ->colors([
                'primary' => '#0c6600',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->maxContentWidth(MaxWidth::Full)
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->font('Poppins')
            ->darkMode(false)
            ->viteTheme('resources/css/filament/app/theme.css');
    }
}
