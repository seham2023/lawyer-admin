<?php

namespace App\Filament\Lawyer\Resources\CaseResource\Pages;

use App\Filament\Lawyer\Resources\CaseResource;
use App\Models\Currency;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\HtmlString;

class ViewCase extends ViewRecord
{
    protected static string $resource = CaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('add_session')
                ->label(__('Add Session'))
                ->icon('heroicon-o-calendar-days')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\TextInput::make('title')
                        ->label(__('title'))
                        ->required()
                        ->maxLength(255),
                    \Filament\Forms\Components\Textarea::make('details')
                        ->label(__('details'))
                        ->columnSpanFull(),
                    \Filament\Forms\Components\DateTimePicker::make('datetime')
                        ->label(__('datetime'))
                        ->required()
                        ->default(now()),
                    \Filament\Forms\Components\Select::make('priority')
                        ->label(__('priority'))
                        ->options([
                            'low' => __('priority_low'),
                            'medium' => __('priority_medium'),
                            'high' => __('priority_high'),
                        ])
                        ->default('medium')
                        ->required(),
                    \Filament\Forms\Components\Select::make('court_id')
                        ->label(__('court'))
                        ->options(\App\Models\Court::where('user_id', auth()->id())->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('judge_name')
                        ->label(__('judge_name'))
                        ->maxLength(255),
                ])
                ->action(function ($record, array $data) {
                    $data['user_id'] = auth()->id();
                    $record->sessions()->create($data);
                    \Filament\Notifications\Notification::make()
                        ->title(__('Session added successfully'))
                        ->success()
                        ->send();
                }),

            Actions\Action::make('add_document')
                ->label(__('Add Document'))
                ->icon('heroicon-o-document-plus')
                ->color('info')
                ->form([
                    \Filament\Forms\Components\TextInput::make('name')
                        ->label(__('name'))
                        ->required()
                        ->maxLength(255),
                    \Filament\Forms\Components\Textarea::make('description')
                        ->label(__('description'))
                        ->required(),
                    \Filament\Forms\Components\FileUpload::make('file_path')
                        ->label(__('file_path'))
                        ->required()
                        ->acceptedFileTypes(['application/pdf', 'image/*'])
                        ->directory('case-documents'),
                ])
                ->action(function ($record, array $data) {
                    $record->documents()->create($data);
                    \Filament\Notifications\Notification::make()
                        ->title(__('Document added successfully'))
                        ->success()
                        ->send();
                }),

            Actions\Action::make('add_payment_detail')
                ->label(__('Add Payment'))
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->visible(fn($record) => $record->payment !== null)
                ->form([
                    \Filament\Forms\Components\TextInput::make('name')
                        ->label(__('Payment Name'))
                        ->placeholder(__('e.g., First Installment'))
                        ->required()
                        ->maxLength(255),

                    \Filament\Forms\Components\Select::make('payment_type')
                        ->label(__('Payment Type'))
                        ->options([
                            'installment' => __('Installment'),
                            'deposit' => __('Deposit'),
                            'final' => __('Final Payment'),
                            'partial' => __('Partial Payment'),
                        ])
                        ->required()
                        ->default('installment'),

                    \Filament\Forms\Components\TextInput::make('amount')
                        ->label(__('Amount'))
                        ->numeric()
                        ->required()
                        ->minValue(0.01)
                        ->rules([
                            function ($record) {
                                return function (string $attribute, $value, \Closure $fail) use ($record) {
                                    if (!$record->payment) return;
                                    
                                    $remaining = $record->payment->remaining_payment;
                                    if ($value > $remaining) {
                                        $fail(__('payment.amount_exceeds_remaining', ['amount' => $remaining]));
                                    }
                                };
                            }
                        ])
                        ->helperText(fn($record) => __('Remaining balance') . ': ' . ($record->payment->remaining_payment ?? 0)),

                    \Filament\Forms\Components\DateTimePicker::make('paid_at')
                        ->label(__('Payment Date'))
                        ->required()
                        ->default(now()),

                    \Filament\Forms\Components\Select::make('pay_method_id')
                        ->label(__('Payment Method'))
                        ->options(\App\Models\PayMethod::all()->pluck('name', 'id'))
                        ->searchable()
                        ->required(),

                    \Filament\Forms\Components\Textarea::make('details')
                        ->label(__('Payment Details'))
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->action(function ($record, array $data) {
                    $data['payment_id'] = $record->payment->id;
                    $record->payment->paymentDetails()->create($data);
                    \Filament\Notifications\Notification::make()
                        ->title(__('Payment added successfully'))
                        ->success()
                        ->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Case Overview
                Infolists\Components\Section::make(__('Case Overview'))
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('case_number')
                                    ->label(__('Case Number'))
                                    ->badge()
                                    ->color('primary')
                                    ->size('lg')
                                    ->weight('bold'),

                                Infolists\Components\TextEntry::make('category.name')
                                    ->label(__('Category'))
                                    ->badge()
                                    ->color('info'),

                                Infolists\Components\TextEntry::make('status.name')
                                    ->label(__('Status'))
                                    ->badge()
                                    ->color(fn($state) => match ($state) {
                                        'Active' => 'success',
                                        'Closed' => 'danger',
                                        'Pending' => 'warning',
                                        default => 'gray',
                                    }),
                            ]),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('start_date')
                                    ->label(__('Start Date'))
                                    ->date()
                                    ->icon('heroicon-o-calendar'),

                                Infolists\Components\TextEntry::make('level.name')
                                    ->label(__('Court Level'))
                                    ->badge()
                                    ->color('warning'),
                            ]),

                        Infolists\Components\TextEntry::make('subject')
                            ->label(__('Subject'))
                            ->columnSpanFull()
                            ->size('lg')
                            ->weight('bold'),

                        Infolists\Components\TextEntry::make('subject_description')
                            ->label(__('Description'))
                            ->columnSpanFull()
                            ->markdown()
                            ->hidden(fn($state) => empty($state)),
                    ])
                    ->columns(1)
                    ->collapsible(),

                // Client Information
                Infolists\Components\Section::make(__('Client Information'))
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('client.first_name')
                                    ->label(__('Client Name'))
                                    ->formatStateUsing(fn($record) => $record->client->first_name . ' ' . $record->client->last_name)
                                    ->icon('heroicon-o-user')
                                    ->weight('bold'),

                                Infolists\Components\TextEntry::make('client.email')
                                    ->label(__('Email'))
                                    ->icon('heroicon-o-envelope')
                                    ->copyable(),

                                Infolists\Components\TextEntry::make('client.phone')
                                    ->label(__('Phone'))
                                    ->icon('heroicon-o-phone')
                                    ->copyable(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                // Opponent Information
                Infolists\Components\Section::make(__('Opponent Information'))
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('opponent.name')
                                    ->label(__('Opponent Name'))
                                    ->icon('heroicon-o-user-minus')
                                    ->default('-'),

                                Infolists\Components\TextEntry::make('opponent.mobile')
                                    ->label(__('Opponent Mobile'))
                                    ->icon('heroicon-o-phone')
                                    ->default('-'),

                                Infolists\Components\TextEntry::make('opponent.email')
                                    ->label(__('Opponent Email'))
                                    ->icon('heroicon-o-envelope')
                                    ->default('-'),

                                Infolists\Components\TextEntry::make('opponent.location')
                                    ->label(__('Opponent Location'))
                                    ->icon('heroicon-o-map-pin')
                                    ->default('-'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->hidden(fn($record) => !$record->opponent),

                // Opponent Lawyer Information
                Infolists\Components\Section::make(__('Opponent Lawyer'))
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('opponentLawyer.name')
                                    ->label(__('Lawyer Name'))
                                    ->icon('heroicon-o-briefcase')
                                    ->default('-'),

                                Infolists\Components\TextEntry::make('opponentLawyer.mobile')
                                    ->label(__('Lawyer Mobile'))
                                    ->icon('heroicon-o-phone')
                                    ->default('-'),

                                Infolists\Components\TextEntry::make('opponentLawyer.email')
                                    ->label(__('Lawyer Email'))
                                    ->icon('heroicon-o-envelope')
                                    ->default('-'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->hidden(fn($record) => !$record->opponentLawyer),

                // Court Information
                Infolists\Components\Section::make(__('Court Information'))
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('currentCourt.court.name')
                                    ->label(__('Court Name'))
                                    ->icon('heroicon-o-building-library')
                                    ->default('-'),

                                Infolists\Components\TextEntry::make('currentCourt.court.location')
                                    ->label(__('Location'))
                                    ->icon('heroicon-o-map-pin')
                                    ->default('-'),

                                Infolists\Components\TextEntry::make('currentCourt.court.court_number')
                                    ->label(__('Court Number'))
                                    ->badge()
                                    ->default('-'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                // Financial Information
                Infolists\Components\Section::make(__('Financial Information'))
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('payment_summary')
                                    ->label(__('Payment Summary'))
                                    ->state(function ($record) {
                                        if (!$record->payment) {
                                            return new HtmlString("
                                                <div class='text-gray-500'>
                                                    <p>" . __('No payment information') . "</p>
                                                </div>
                                            ");
                                        }

                                        $totalAmount = $record->payment->amount ?? 0;
                                        $paidAmount = $record->payment->total_paid ?? 0;
                                        $remaining = $totalAmount - $paidAmount;
                                        $currency = \App\Support\Money::getCurrencySymbol();

                                        return new HtmlString("
                                            <div class='space-y-3'>
                                                <div class='flex justify-between items-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg'>
                                                    <span class='font-medium text-blue-700 dark:text-blue-300'>" . __('Total Amount') . ":</span>
                                                    <span class='font-bold text-blue-900 dark:text-blue-100 text-lg'>{$totalAmount} {$currency}</span>
                                                </div>
                                                <div class='flex justify-between items-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg'>
                                                    <span class='font-medium text-green-700 dark:text-green-300'>" . __('Paid') . ":</span>
                                                    <span class='font-bold text-green-900 dark:text-green-100 text-lg'>{$paidAmount} {$currency}</span>
                                                </div>
                                                <div class='flex justify-between items-center p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border-2 border-red-200 dark:border-red-800'>
                                                    <span class='font-medium text-red-700 dark:text-red-300'>" . __('Remaining') . ":</span>
                                                    <span class='font-bold text-red-900 dark:text-red-100 text-xl'>{$remaining} {$currency}</span>
                                                </div>
                                            </div>
                                        ");
                                    })
                                    ->columnSpan(2),

                                Infolists\Components\TextEntry::make('payment_details')
                                    ->label(__('Payment Details'))
                                    ->state(function ($record) {
                                        if (!$record->payment) {
                                            return '-';
                                        }

                                        $currency = $record->payment->currency->name ?? '';
                                        $method = $record->payment->payMethod->name ?? '-';
                                        $status = $record->payment->status->name ?? '-';
                                        $tax = $record->payment->tax ?? 0;

                                        return new HtmlString("
                                            <div class='space-y-2'>
                                                <div class='flex justify-between'>
                                                    <span class='text-gray-600 dark:text-gray-400'>" . __('Currency') . ":</span>
                                                    <span class='font-medium'>{$currency}</span>
                                                </div>
                                                <div class='flex justify-between'>
                                                    <span class='text-gray-600 dark:text-gray-400'>" . __('Tax') . ":</span>
                                                    <span class='font-medium'>{$tax}%</span>
                                                </div>
                                                <div class='flex justify-between'>
                                                    <span class='text-gray-600 dark:text-gray-400'>" . __('Method') . ":</span>
                                                    <span class='font-medium'>{$method}</span>
                                                </div>
                                                <div class='flex justify-between'>
                                                    <span class='text-gray-600 dark:text-gray-400'>" . __('Status') . ":</span>
                                                    <span class='font-medium'>{$status}</span>
                                                </div>
                                            </div>
                                        ");
                                    })
                                    ->columnSpan(1),
                            ]),
                    ]),

                // Additional Information
                Infolists\Components\Section::make(__('Additional Information'))
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label(__('Notes'))
                            ->markdown()
                            ->columnSpanFull()
                            ->hidden(fn($state) => empty($state)),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label(__('Created At'))
                                    ->dateTime()
                                    ->icon('heroicon-o-clock'),

                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label(__('Updated At'))
                                    ->dateTime()
                                    ->icon('heroicon-o-clock')
                                    ->since(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
