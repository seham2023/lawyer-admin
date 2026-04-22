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
            ->login(\App\Filament\Auth\Login::class)
            ->brandLogo(asset('images/legal/logo.png'))
            ->brandLogoHeight('3.5rem')
            ->favicon(asset('images/legal/gavel.png'))
            ->colors([
                'primary' => [
                    50 => '#f9fafb',
                    100 => '#f3f4f6',
                    200 => '#e5e7eb',
                    300 => '#d1d5db',
                    400 => '#9ca3af',
                    500 => '#d4af37', // Metallic Gold Primary
                    600 => '#c5a028',
                    700 => '#a6851e',
                    800 => '#82671a',
                    900 => '#6a5418',
                    950 => '#3f310b',
                ],
                'danger' => Color::Rose,
                'gray' => Color::Slate,
                'info' => Color::Cyan,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
            ])
            ->font('Outfit')
            ->spa()
            ->databaseNotifications()
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->discoverResources(in: app_path('Filament/Lawyer/Resources'), for: 'App\\Filament\\Lawyer\\Resources')
            ->discoverPages(in: app_path('Filament/Lawyer/Pages'), for: 'App\\Filament\\Lawyer\\Pages')
            ->pages([
                \App\Filament\Lawyer\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Lawyer/Widgets'), for: 'App\\Filament\\Lawyer\\Widgets')
            ->widgets([
                \App\Filament\Lawyer\Widgets\UnreadMessagesWidget::class,
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
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
            <!-- Google Fonts: Montserrat -->
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

            <!-- Socket.IO Client -->
            <script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
            
            <!-- Socket Client Wrapper (SPA-safe, handles all event bridging to Livewire) -->
            <script src="{{ asset(\'js/socket-client.js\') }}?v={{ time() }}"></script>
            
            <!-- Meta tags for Socket.IO -->
            <meta name="user-id" content="{{ auth()->id() }}">
            <meta name="socket-url" content="{{ env(\'SOCKET_URL\', \'https://qestass.com:4888\') }}">
        '));

        FilamentView::registerRenderHook('panels::body.end', fn(): string => Blade::render("@vite('resources/css/admin.scss')"));
        // FilamentView::registerRenderHook('panels::body.end', fn(): string => Blade::render("@vite('resources/css/demo.scss')"));
        FilamentView::registerRenderHook('panels::body.end', fn(): string => Blade::render("@vite('resources/js/app.js')"));
        
        // Global Call Notification Component
        // socket-client.js already dispatches 'incoming-call' and 'call-ended-remote'
        // events to Livewire automatically — no JS bridge needed here
        FilamentView::registerRenderHook('panels::body.start', fn(): string => Blade::render('
            @livewire(\'call-notification\')
        '));
    }
}
