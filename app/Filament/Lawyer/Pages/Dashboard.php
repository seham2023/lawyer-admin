<?php

namespace App\Filament\Lawyer\Pages;

use Filament\Pages\Dashboard as FilamentDashboard;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use App\Filament\Lawyer\Widgets\StatsOverviewWidget;
use App\Filament\Lawyer\Widgets\CaseRecordsOverviewWidget;
use App\Filament\Lawyer\Widgets\ClientsOverviewWidget;
use App\Filament\Lawyer\Widgets\CalendarWidget;

class Dashboard extends FilamentDashboard
{
    // public static function canAccess(): bool
    // {
    //     return auth()->user()->checkPermissionTo('view Dashboard');
    // }

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $routePath = '/';

    public static function getNavigationLabel(): string
    {
        return __('Dashboard');
    }

    public function getTitle(): string
    {
        return __('Dashboard');
    }

    public function getWidgets(): array
    {
        return [
            // AccountWidget::class,
            StatsOverviewWidget::class,
            CalendarWidget::class,
            // CaseRecordsOverviewWidget::class,
            // ClientsOverviewWidget::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }
}
