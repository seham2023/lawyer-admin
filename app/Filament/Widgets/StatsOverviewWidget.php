<?php

namespace App\Filament\Widgets;

use App\Models\Qestass\User;
use App\Models\CaseRecord;
use App\Models\Payment;
use App\Support\LawyerUserAccess;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $lawyerId = auth()->id();
        
        // Financial metrics
        $totalPayments = \App\Models\Payment::where('user_id', $lawyerId)->sum('amount');
        $totalCollected = \App\Models\PaymentDetail::whereHas('payment', fn($q) => $q->where('user_id', $lawyerId))->sum('amount');
        $outstanding = max(0, $totalPayments - $totalCollected);
        
        return [
            Stat::make(__('Total Clients'), LawyerUserAccess::applyToUserQuery(User::query(), $lawyerId, 'client')->count())
                ->description(__('Attached to your workspace'))
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make(__('Active Cases'), \App\Models\CaseRecord::where('user_id', $lawyerId)->where('status_id', '!=', 4)->count()) // Assuming 4 is 'Closed'
                ->description(__('Ongoing legal matters'))
                ->descriptionIcon('heroicon-m-scale')
                ->color('info'),

            Stat::make(__('Total Collected'), number_format($totalCollected, 2) . ' ' . \App\Support\Money::getCurrencyCode())
                ->description(__('Revenue from payments'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make(__('Outstanding'), number_format($outstanding, 2) . ' ' . \App\Support\Money::getCurrencyCode())
                ->description(__('Pending client dues'))
                ->descriptionIcon('heroicon-m-clock')
                ->color($outstanding > 0 ? 'danger' : 'success'),
        ];
    }
}
