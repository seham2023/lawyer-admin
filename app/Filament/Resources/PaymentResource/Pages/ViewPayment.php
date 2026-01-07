<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Payment Overview Section
                Section::make(__('Payment Overview'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('payable_type')
                                    ->label(__('Payment For'))
                                    ->formatStateUsing(fn($state) => match ($state) {
                                        'App\\Models\\CaseRecord' => __('Case'),
                                        'App\\Models\\Visit' => __('Visit'),
                                        default => __('Unknown'),
                                    })
                                    ->badge()
                                    ->color(fn($state) => match ($state) {
                                        'App\\Models\\CaseRecord' => 'info',
                                        'App\\Models\\Visit' => 'success',
                                        default => 'gray',
                                    }),

                                TextEntry::make('payable.subject')
                                    ->label(__('Related To'))
                                    ->getStateUsing(function ($record) {
                                        if ($record->payable_type === 'App\\Models\\CaseRecord') {
                                            return $record->payable?->subject ?? '-';
                                        } elseif ($record->payable_type === 'App\\Models\\Visit') {
                                            return $record->payable?->purpose ?? '-';
                                        }
                                        return '-';
                                    })
                                    ->icon('heroicon-o-document-text')
                                    ->columnSpan(2),
                            ]),
                    ])
                    ->columns(3)
                    ->collapsible(),

                // Financial Summary Section
                Section::make(__('Financial Summary'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('amount')
                                    ->label(__('Total Amount'))
                                    ->money(fn($record) => $record->currency?->code ?? 'USD')
                                    ->icon('heroicon-o-banknotes')
                                    ->color('info')
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->weight('bold'),

                                TextEntry::make('total_paid')
                                    ->label(__('Paid Amount'))
                                    ->money(fn($record) => $record->currency?->code ?? 'USD')
                                    ->icon('heroicon-o-check-circle')
                                    ->color('success')
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->weight('bold'),

                                TextEntry::make('remaining_payment')
                                    ->label(__('Remaining Balance'))
                                    ->money(fn($record) => $record->currency?->code ?? 'USD')
                                    ->icon('heroicon-o-clock')
                                    ->color(fn($record) => $record->remaining_payment > 0 ? 'danger' : 'success')
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->weight('bold'),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('tax')
                                    ->label(__('Tax (%)'))
                                    ->suffix('%')
                                    ->icon('heroicon-o-calculator'),

                                TextEntry::make('currency.name')
                                    ->label(__('Currency'))
                                    ->icon('heroicon-o-currency-dollar'),

                                TextEntry::make('payMethod.name')
                                    ->label(__('Payment Method'))
                                    ->icon('heroicon-o-credit-card'),
                            ]),
                    ])
                    ->columns(3)
                    ->collapsible(),

                // Payment Status Section
                Section::make(__('Payment Status'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('status.name')
                                    ->label(__('Status'))
                                    ->badge()
                                    ->color(fn($record) => match ($record->status?->name) {
                                        'Paid' => 'success',
                                        'Pending' => 'warning',
                                        'Cancelled' => 'danger',
                                        default => 'gray',
                                    })
                                    ->icon('heroicon-o-information-circle')
                                    ->hidden(fn($record) => !$record->status),

                                ImageEntry::make('image')
                                    ->label(__('Payment Receipt'))
                                    ->hidden(fn($record) => !$record->image),
                            ]),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->hidden(fn($record) => !$record->status && !$record->image),

                // Additional Information Section
                Section::make(__('Additional Information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label(__('Created At'))
                                    ->dateTime()
                                    ->icon('heroicon-o-clock'),
                                TextEntry::make('updated_at')
                                    ->label(__('Last Updated'))
                                    ->dateTime()
                                    ->icon('heroicon-o-arrow-path'),
                            ]),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
