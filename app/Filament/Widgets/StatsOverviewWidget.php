<?php

namespace App\Filament\Widgets;

use App\Models\User;
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
            Stat::make(__('Total Users'), User::count())
                ->description(__('All registered users'))
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make(__('Active Cases'), CaseRecord::count())
                ->description(__('Currently active legal cases'))
                ->descriptionIcon('heroicon-m-scale')
                ->color('info'),

            Stat::make(__('Categories'), Category::count())
                ->description(__('Total categories in system'))
                ->descriptionIcon('heroicon-m-folder')
                ->color('warning'),

            Stat::make(__('Total Payments'), Payment::count())
                ->description(__('Payment records'))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),
        ];
    }
}
