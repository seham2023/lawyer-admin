<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\CaseRecord;
use App\Models\Category;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Clients', Client::count())
                ->description('All registered clients')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            
            Stat::make('Active Cases', CaseRecord::count())
                ->description('Currently active legal cases')
                ->descriptionIcon('heroicon-m-scale')
                ->color('info'),
            
            Stat::make('Categories', Category::count())
                ->description('Total categories in system')
                ->descriptionIcon('heroicon-m-folder')
                ->color('warning'),
            
            Stat::make('Total Payments', Payment::count())
                ->description('Payment records')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),
        ];
    }
}
