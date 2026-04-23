<?php

namespace App\Filament\Lawyer\Widgets;

use App\Models\Qestass\User;
use App\Models\CaseRecord;
use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Models\Session;
use App\Models\Visit;
use App\Models\Expense;
use App\Models\Reminder;
use App\Models\Document;
use App\Models\Note;
use App\Support\LawyerUserAccess;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Filament\Lawyer\Resources\ClientResource;
use App\Filament\Lawyer\Resources\CaseResource;
use App\Filament\Lawyer\Resources\PaymentResource;
use App\Filament\Lawyer\Resources\SubLawyerResource;
use App\Filament\Lawyer\Resources\VisitResource;
use App\Filament\Lawyer\Resources\ExpenseResource;
use App\Filament\Lawyer\Resources\NoteResource;
use Carbon\Carbon;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $lawyerId = auth()->id();
        $currency = Money::getCurrencyCode();
        
        // --- Financial Metrics ---
        $totalPayments = Payment::where('user_id', $lawyerId)->sum('amount');
        $totalCollected = PaymentDetail::whereHas('payment', fn($q) => $q->where('user_id', $lawyerId))->sum('amount');
        $totalExpenses = Payment::where('user_id', $lawyerId)
            ->where('payable_type', Expense::class)
            ->sum('amount');
            
        $outstanding = max(0, $totalPayments - $totalCollected);
        $netProfit = $totalCollected - $totalExpenses;
        
        // --- Case Metrics ---
        // New cases this month
        $newCasesMonth = CaseRecord::where('user_id', $lawyerId)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // Active cases (excluding final statuses)
        $activeCasesCount = CaseRecord::where('user_id', $lawyerId)
            ->whereNotIn('status_id', [14, 15, 33, 34])
            ->count();
            
        // Cases specifically in litigation (ID 9)
        $litigationCount = CaseRecord::where('user_id', $lawyerId)
            ->where('status_id', 9)
            ->count();

        // Cases in Appeal (ID 12)
        $appealCount = CaseRecord::where('user_id', $lawyerId)
            ->where('status_id', 12)
            ->count();

        // Closed cases (ID 14)
        $closedCasesCount = CaseRecord::where('user_id', $lawyerId)
            ->where('status_id', 14)
            ->count();

        // --- Schedule & Activity Metrics ---
        $upcomingSessionsCount = Session::where('user_id', $lawyerId)
            ->whereBetween('datetime', [now(), now()->addDays(7)])
            ->count();

        $pendingVisitsCount = Visit::where('user_id', $lawyerId)
            ->where('visit_date', '>=', now())
            ->count();
            
        $activeRemindersCount = Reminder::where('user_id', $lawyerId)
            ->where('status', 'pending')
            ->count();

        $totalDocumentsCount = Document::where('user_id', $lawyerId)->count();
        $totalNotesCount = Note::where('user_id', $lawyerId)->count();

        $subLawyerIds = LawyerUserAccess::userIdsForLawyer($lawyerId, 'sub_lawyer');
        $subLawyersCount = count($subLawyerIds);

        return [
            // --- Row 1: Primary Overview ---
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

            Stat::make(__('New Cases (Month)'), $newCasesMonth)
                ->description(__('Growth this month'))
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('success')
                ->url(CaseResource::getUrl()),

            Stat::make(__('In Litigation'), $litigationCount)
                ->description(__('Active in court'))
                ->descriptionIcon('heroicon-m-building-library')
                ->color('warning')
                ->url(CaseResource::getUrl()),

            // --- Row 2: Financials ---
            Stat::make(__('Total Collected'), number_format($totalCollected, 2) . ' ' . $currency)
                ->description(__('Revenue from payments'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->url(PaymentResource::getUrl()),

            Stat::make(__('Total Expenses'), number_format($totalExpenses, 2) . ' ' . $currency)
                ->description(__('Business costs'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('danger')
                ->url(ExpenseResource::getUrl()),

            Stat::make(__('Net Profit'), number_format($netProfit, 2) . ' ' . $currency)
                ->description(__('Revenue after expenses'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($netProfit >= 0 ? 'success' : 'danger'),

            Stat::make(__('Outstanding'), number_format($outstanding, 2) . ' ' . $currency)
                ->description(__('Pending client dues'))
                ->descriptionIcon('heroicon-m-clock')
                ->color($outstanding > 0 ? 'danger' : 'success')
                ->url(PaymentResource::getUrl()),

            // --- Row 3: Schedule & Management ---
            Stat::make(__('Upcoming Sessions'), $upcomingSessionsCount)
                ->description(__('Hearings in next 7 days'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->url(CaseResource::getUrl()),

            Stat::make(__('Pending Visits'), $pendingVisitsCount)
                ->description(__('Client consultations'))
                ->descriptionIcon('heroicon-m-calendar')
                ->url(VisitResource::getUrl()),

            Stat::make(__('Active Reminders'), $activeRemindersCount)
                ->description(__('Pending notifications'))
                ->descriptionIcon('heroicon-m-bell-alert')
                ->color($activeRemindersCount > 0 ? 'warning' : 'gray'),

            Stat::make(__('Sub-Lawyers'), $subLawyersCount)
                ->description(__('Members of your team'))
                ->descriptionIcon('heroicon-m-identification')
                ->url(SubLawyerResource::getUrl()),

            // --- Row 4: Resources ---
            Stat::make(__('Closed Cases'), $closedCasesCount)
                ->description(__('Completed lifecycle'))
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('gray')
                ->url(CaseResource::getUrl()),

            Stat::make(__('Total Documents'), $totalDocumentsCount)
                ->description(__('Case files & uploads'))
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make(__('Appeals'), $appealCount)
                ->description(__('Current appellate cases'))
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning')
                ->url(CaseResource::getUrl()),

            Stat::make(__('Notes & Tasks'), $totalNotesCount)
                ->description(__('Internal records'))
                ->descriptionIcon('heroicon-m-pencil-square')
                ->url(NoteResource::getUrl()),
        ];
    }
}
