<?php

namespace App\Filament\Lawyer\Widgets;

use App\Models\Qestass\User;
use App\Models\CaseRecord;
use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Models\Session;
use App\Support\LawyerUserAccess;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Filament\Lawyer\Resources\ClientResource;
use App\Filament\Lawyer\Resources\CaseResource;
use App\Filament\Lawyer\Resources\PaymentResource;
use App\Filament\Lawyer\Resources\SessionResource;
use App\Filament\Lawyer\Resources\SubLawyerResource;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $lawyerId = auth()->id();
        
        // Financial metrics
        $totalPayments = Payment::where('user_id', $lawyerId)->sum('amount');
        $totalCollected = PaymentDetail::whereHas('payment', fn($q) => $q->where('user_id', $lawyerId))->sum('amount');
        $outstanding = max(0, $totalPayments - $totalCollected);
        
        // Operational metrics
        $activeCasesCount = CaseRecord::where('user_id', $lawyerId)
            ->whereNotIn('status_id', [14, 15, 33, 34]) // Closed, Archived, Completed, Cancelled
            ->count();
            
        $upcomingSessionsCount = Session::where('user_id', $lawyerId)
            ->whereBetween('datetime', [now(), now()->addDays(7)])
            ->count();

        $subLawyerIds = LawyerUserAccess::userIdsForLawyer($lawyerId, 'sub_lawyer');
        $subLawyersCount = count($subLawyerIds);

        $currency = Money::getCurrencyCode();
        
        return [
            Stat::make(__('Total Clients'), LawyerUserAccess::applyToUserQuery(User::query(), $lawyerId, 'client')->count())
                ->description(__('Attached to your workspace'))
                ->descriptionIcon('heroicon-m-users')
                ->color('success')
                ->url(ClientResource::getUrl()),

            Stat::make(__('Active Cases'), $activeCasesCount)
                ->description(__('Ongoing legal matters'))
                ->descriptionIcon('heroicon-m-scale')
                ->color('info')
                ->url(CaseResource::getUrl()),

            // Stat::make(__('Upcoming Sessions'), $upcomingSessionsCount)
            //     ->description(__('Scheduled for next 7 days'))
            //     ->descriptionIcon('heroicon-m-calendar-days')
            //     ->color('warning')
            //     ->url(route('filament.admin.resources.sessions.index')), // Using route() for session resource

            Stat::make(__('Total Collected'), number_format($totalCollected, 2) . ' ' . $currency)
                ->description(__('Revenue from payments'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->url(PaymentResource::getUrl()),

            Stat::make(__('Outstanding'), number_format($outstanding, 2) . ' ' . $currency)
                ->description(__('Pending client dues'))
                ->descriptionIcon('heroicon-m-clock')
                ->color($outstanding > 0 ? 'danger' : 'success')
                ->url(PaymentResource::getUrl()),

            Stat::make(__('Sub-Lawyers'), $subLawyersCount)
                ->description(__('Members of your legal team'))
                ->descriptionIcon('heroicon-m-identification')
                ->color('info')
                ->url(SubLawyerResource::getUrl()),
        ];
    }
}
