<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Blade;
use App\Filament\Resources\CaseResource;
use App\Filament\Resources\ClientResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Support\Facades\FilamentView;
use Filament\SpatieLaravelTranslatablePlugin;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Kenepa\TranslationManager\TranslationManagerPlugin;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Althinect\FilamentSpatieRolesPermissions\FilamentSpatieRolesPermissionsPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandLogo(asset('images/legal/logo.png'))
            ->brandLogoHeight('3.5rem')
            ->favicon(asset('images/legal/gavel.png'))
            ->colors([
                'primary' => [
                    50 => '#fbf8ef',
                    100 => '#f5ecd6',
                    200 => '#ebd9ab',
                    300 => '#dec17b',
                    400 => '#d1a555',
                    500 => '#c58e3e',
                    600 => '#aa7233',
                    700 => '#8d5a2d',
                    800 => '#744a29',
                    900 => '#603d25',
                    950 => '#351f12',
                ],
                'danger' => Color::Rose,
                'gray' => Color::Zinc,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            ->font('Outfit')
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                // Custom Dashboard will be auto-discovered
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\UnreadMessagesWidget::class,
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->globalSearch(true)
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
            ->plugins([
                FilamentSpatieRolesPermissionsPlugin::make(),
                TranslationManagerPlugin::make(),
                SpatieLaravelTranslatablePlugin::make()
                    ->defaultLocales(['ar', 'en']),
                FilamentFullCalendarPlugin::make()
                    ->selectable()
                    ->editable()
                    ->timezone('auto')
                    ->editable(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
        ;
    }


    public function register(): void
    {
        parent::register();

        // Add Socket.IO and custom scripts to head
        FilamentView::registerRenderHook('panels::head.end', fn(): string => Blade::render('
            <!-- Socket.IO Client -->
            <script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
            
            <!-- Socket Client Wrapper -->
            <script src="{{ asset(\'js/socket-client.js\') }}"></script>
            
            <!-- Meta tags for Socket.IO -->
            <meta name="user-id" content="{{ auth()->id() }}">
            <meta name="socket-url" content="{{ env(\'SOCKET_URL\', \'https://qestass.com:4888\') }}">
        '));

        FilamentView::registerRenderHook('panels::body.end', fn(): string => Blade::render("@vite('resources/css/admin.scss')"));
        // FilamentView::registerRenderHook('panels::body.end', fn(): string => Blade::render("@vite('resources/css/demo.scss')"));
        FilamentView::registerRenderHook('panels::body.end', fn(): string => Blade::render("@vite('resources/js/app.js')"));
    }
}
