<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\CaseResource;
use App\Filament\Resources\ClientResource;
use App\Models\Currency;
use App\Models\Status;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\HtmlString;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('add_visit')
                ->label(__('Add Visit'))
                ->icon('heroicon-o-calendar')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\DateTimePicker::make('visit_date')
                        ->label(__('Visit Date'))
                        ->required()
                        ->default(now())
                        ->native(false),
                    \Filament\Forms\Components\TextInput::make('purpose')
                        ->label(__('Purpose'))
                        ->required()
                        ->maxLength(255),
                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label(__('Notes'))
                        ->rows(3)
                        ->columnSpanFull(),

                    // Payment Section
                    \Filament\Forms\Components\Section::make(__('Payment Information'))
                        ->schema([
                            \Filament\Forms\Components\Select::make('currency_id')
                                ->label(__('Currency'))
                                ->options(Currency::all()->pluck('name', 'id'))
                                ->searchable()
                                ->required()
                                ->default(1),

                            \Filament\Forms\Components\TextInput::make('amount')
                                ->label(__('Amount'))
                                ->numeric()
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $tax = $get('tax') ?? 0;
                                    $amount = $state ?? 0;
                                    $total = $amount + ($amount * $tax / 100);
                                    $set('total_after_tax', $total);
                                }),

                            \Filament\Forms\Components\TextInput::make('tax')
                                ->label(__('Tax') . ' (%)')
                                ->numeric()
                                ->default(0)
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $amount = $get('amount') ?? 0;
                                    $tax = $state ?? 0;
                                    $total = $amount + ($amount * $tax / 100);
                                    $set('total_after_tax', $total);
                                }),

                            \Filament\Forms\Components\TextInput::make('total_after_tax')
                                ->label(__('Total After Tax'))
                                ->numeric()
                                ->disabled()
                                ->dehydrated(false),

                            \Filament\Forms\Components\Select::make('pay_method_id')
                                ->label(__('Payment Method'))
                                ->options(\App\Models\PayMethod::all()->pluck('name', 'id'))
                                ->searchable(),

                            \Filament\Forms\Components\Select::make('payment_status_id')
                                ->label(__('Payment Status'))
                                ->options(Status::where('type', 'payment')->pluck('name', 'id'))
                                ->searchable()
                                ->default(1),
                        ])
                        ->columns(2)
                        ->collapsible(),
                ])
                ->action(function (array $data) {
                    // Create visit
                    $visit = \App\Models\Visit::create([
                        'user_id' => auth()->id(),
                        'client_id' => $this->record->id,
                        'visit_date' => $data['visit_date'],
                        'purpose' => $data['purpose'],
                        'notes' => $data['notes'] ?? null,
                    ]);

                    // Create payment if amount is provided
                    if (isset($data['amount']) && $data['amount'] > 0) {
                        $totalAmount = $data['amount'] + ($data['amount'] * ($data['tax'] ?? 0) / 100);

                        \App\Models\Payment::create([
                            'amount' => $totalAmount,
                            'tax' => $data['tax'] ?? 0,
                            'currency_id' => $data['currency_id'],
                            'user_id' => auth()->id(),
                            'client_id' => $this->record->id,
                            'payment_date' => now(),
                            'pay_method_id' => $data['pay_method_id'] ?? null,
                            'status_id' => $data['payment_status_id'] ?? 1,
                            'payable_type' => \App\Models\Visit::class,
                            'payable_id' => $visit->id,
                        ]);
                    }

                    \Filament\Notifications\Notification::make()
                        ->title(__('Visit created successfully'))
                        ->success()
                        ->send();
                }),
            Actions\Action::make('add_case')
                ->label(__('Add Case'))
                ->icon('heroicon-o-briefcase')
                ->color('warning')
                ->url(fn() => CaseResource::getUrl('create', ['client_id' => $this->record->id])),
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Client Information
                Infolists\Components\Section::make(__('Client Information'))
                    ->schema([
                        Infolists\Components\TextEntry::make('first_name')
                            ->label(__('First Name')),
                        Infolists\Components\TextEntry::make('last_name')
                            ->label(__('Last Name')),
                        Infolists\Components\TextEntry::make('email')
                            ->label(__('Email'))
                            ->icon('heroicon-o-envelope'),
                        Infolists\Components\TextEntry::make('phone')
                            ->label(__('Mobile'))
                            ->icon('heroicon-o-phone'),
                        Infolists\Components\TextEntry::make('gender')
                            ->label(__('Gender'))
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'male' => 'info',
                                'female' => 'success',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('address')
                            ->label(__('Address'))
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('notes')
                            ->label(__('Notes'))
                            ->columnSpanFull()
                            ->hidden(fn($state) => empty($state)),
                    ])
                    ->columns(2),

                // Financial Overview
                Infolists\Components\Section::make(__('Financial Overview'))
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                // Cases Financial Summary
                                Infolists\Components\TextEntry::make('cases_financial')
                                    ->label(__('Cases Financial Summary'))
                                    ->state(function ($record) {
                                        $cases = $record->caseRecords()->with('payment')->get();
                                        $totalAmount = 0;
                                        $paidAmount = 0;

                                        foreach ($cases as $case) {
                                            if ($case->payment) {
                                                $totalAmount += $case->payment->amount ?? 0;
                                                $paidAmount += $case->payment->total_paid ?? 0;
                                            }
                                        }

                                        $remaining = $totalAmount - $paidAmount;
                                        $currency = Currency::first()->symbol ?? '';

                                        return new HtmlString("
                                            <div class='space-y-2'>
                                                <div class='flex justify-between'>
                                                    <span class='font-medium'>" . __('Total') . ":</span>
                                                    <span class='font-bold text-blue-600'>{$totalAmount} {$currency}</span>
                                                </div>
                                                <div class='flex justify-between'>
                                                    <span class='font-medium'>" . __('Paid') . ":</span>
                                                    <span class='font-bold text-green-600'>{$paidAmount} {$currency}</span>
                                                </div>
                                                <div class='flex justify-between border-t pt-2'>
                                                    <span class='font-medium'>" . __('Remaining') . ":</span>
                                                    <span class='font-bold text-red-600'>{$remaining} {$currency}</span>
                                                </div>
                                            </div>
                                        ");
                                    })
                                    ->columnSpan(1),

                                // Visits Financial Summary
                                Infolists\Components\TextEntry::make('visits_financial')
                                    ->label(__('Visits Financial Summary'))
                                    ->state(function ($record) {
                                        $visits = $record->visits()->with('payment')->get();
                                        $totalAmount = 0;
                                        $paidAmount = 0;

                                        foreach ($visits as $visit) {
                                            if ($visit->payment) {
                                                $totalAmount += $visit->payment->amount ?? 0;
                                                $paidAmount += $visit->payment->total_paid ?? 0;
                                            }
                                        }

                                        $remaining = $totalAmount - $paidAmount;
                                        $currency = Currency::first()->symbol ?? '';

                                        return new HtmlString("
                                            <div class='space-y-2'>
                                                <div class='flex justify-between'>
                                                    <span class='font-medium'>" . __('Total') . ":</span>
                                                    <span class='font-bold text-blue-600'>{$totalAmount} {$currency}</span>
                                                </div>
                                                <div class='flex justify-between'>
                                                    <span class='font-medium'>" . __('Paid') . ":</span>
                                                    <span class='font-bold text-green-600'>{$paidAmount} {$currency}</span>
                                                </div>
                                                <div class='flex justify-between border-t pt-2'>
                                                    <span class='font-medium'>" . __('Remaining') . ":</span>
                                                    <span class='font-bold text-red-600'>{$remaining} {$currency}</span>
                                                </div>
                                            </div>
                                        ");
                                    })
                                    ->columnSpan(1),

                                // Total Financial Summary
                                Infolists\Components\TextEntry::make('total_financial')
                                    ->label(__('Total Financial Summary'))
                                    ->state(function ($record) {
                                        // Cases
                                        $cases = $record->caseRecords()->with('payment')->get();
                                        $casesTotalAmount = 0;
                                        $casesPaidAmount = 0;

                                        foreach ($cases as $case) {
                                            if ($case->payment) {
                                                $casesTotalAmount += $case->payment->amount ?? 0;
                                                $casesPaidAmount += $case->payment->total_paid ?? 0;
                                            }
                                        }

                                        // Visits
                                        $visits = $record->visits()->with('payment')->get();
                                        $visitsTotalAmount = 0;
                                        $visitsPaidAmount = 0;

                                        foreach ($visits as $visit) {
                                            if ($visit->payment) {
                                                $visitsTotalAmount += $visit->payment->amount ?? 0;
                                                $visitsPaidAmount += $visit->payment->total_paid ?? 0;
                                            }
                                        }

                                        $totalAmount = $casesTotalAmount + $visitsTotalAmount;
                                        $paidAmount = $casesPaidAmount + $visitsPaidAmount;
                                        $remaining = $totalAmount - $paidAmount;
                                        $currency = Currency::first()->symbol ?? '';

                                        return new HtmlString("
                                            <div class='space-y-2 bg-gray-50 dark:bg-gray-800 p-4 rounded-lg'>
                                                <div class='flex justify-between'>
                                                    <span class='font-medium'>" . __('Total') . ":</span>
                                                    <span class='font-bold text-blue-600 text-lg'>{$totalAmount} {$currency}</span>
                                                </div>
                                                <div class='flex justify-between'>
                                                    <span class='font-medium'>" . __('Paid') . ":</span>
                                                    <span class='font-bold text-green-600 text-lg'>{$paidAmount} {$currency}</span>
                                                </div>
                                                <div class='flex justify-between border-t pt-2'>
                                                    <span class='font-medium'>" . __('Remaining') . ":</span>
                                                    <span class='font-bold text-red-600 text-lg'>{$remaining} {$currency}</span>
                                                </div>
                                            </div>
                                        ");
                                    })
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }
}
