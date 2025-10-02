<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as FilamentDashboard;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\CaseRecordsOverviewWidget;
use App\Filament\Widgets\ClientsOverviewWidget;
use App\Filament\Widgets\CalendarWidget;

class Dashboard extends FilamentDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    protected static string $routePath = '/';
    
    protected static ?string $title = 'Dashboard';

    public function getWidgets(): array
    {
        return [
            AccountWidget::class,
            StatsOverviewWidget::class,
            CalendarWidget::class,
            CaseRecordsOverviewWidget::class,
            ClientsOverviewWidget::class,
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
