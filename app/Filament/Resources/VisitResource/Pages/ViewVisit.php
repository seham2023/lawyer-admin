<?php

namespace App\Filament\Resources\VisitResource\Pages;

use App\Filament\Resources\VisitResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;

class ViewVisit extends ViewRecord
{
    protected static string $resource = VisitResource::class;

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
                // Visit Overview Section
                Section::make(__('Visit Overview'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('purpose')
                                    ->label(__('Purpose'))
                                    ->icon('heroicon-o-document-text')
                                    ->columnSpan(2),
                                TextEntry::make('visit_date')
                                    ->label(__('Visit Date'))
                                    ->date()
                                    ->icon('heroicon-o-calendar'),
                            ]),
                        TextEntry::make('notes')
                            ->label(__('Notes'))
                            ->markdown()
                            ->columnSpanFull()
                            ->hidden(fn($record) => empty($record->notes)),
                    ])
                    ->columns(3)
                    ->collapsible(),

                // Client Information Section
                Section::make(__('Client Information'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('client.name')
                                    ->label(__('Client Name'))
                                    ->icon('heroicon-o-user'),
                                TextEntry::make('client.email')
                                    ->label(__('Email'))
                                    ->icon('heroicon-o-envelope')
                                    ->copyable()
                                    ->hidden(fn($record) => empty($record->client?->email)),
                                TextEntry::make('client.phone')
                                    ->label(__('Phone'))
                                    ->icon('heroicon-o-phone')
                                    ->copyable()
                                    ->hidden(fn($record) => empty($record->client?->phone)),
                            ]),
                    ])
                    ->columns(3)
                    ->collapsible(),

                // Financial Information Section
                Section::make(__('Financial Information'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('payment.amount')
                                    ->label(__('Total Amount'))
                                    ->money(fn($record) => $record->payment?->currency?->code ?? 'USD')
                                    ->icon('heroicon-o-banknotes')
                                    ->color('info')
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->weight('bold')
                                    ->hidden(fn($record) => !$record->payment),

                                TextEntry::make('payment.total_paid')
                                    ->label(__('Paid Amount'))
                                    ->money(fn($record) => $record->payment?->currency?->code ?? 'USD')
                                    ->icon('heroicon-o-check-circle')
                                    ->color('success')
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->weight('bold')
                                    ->hidden(fn($record) => !$record->payment),

                                TextEntry::make('payment.remaining_payment')
                                    ->label(__('Remaining Balance'))
                                    ->money(fn($record) => $record->payment?->currency?->code ?? 'USD')
                                    ->icon('heroicon-o-clock')
                                    ->color(fn($record) => $record->payment?->remaining_payment > 0 ? 'danger' : 'success')
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->weight('bold')
                                    ->hidden(fn($record) => !$record->payment),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('payment.currency.name')
                                    ->label(__('Currency'))
                                    ->icon('heroicon-o-currency-dollar')
                                    ->hidden(fn($record) => !$record->payment),

                                TextEntry::make('payment.payMethod.name')
                                    ->label(__('Payment Method'))
                                    ->icon('heroicon-o-credit-card')
                                    ->hidden(fn($record) => !$record->payment?->payMethod),

                                TextEntry::make('payment.status.name')
                                    ->label(__('Payment Status'))
                                    ->badge()
                                    ->color(fn($record) => match ($record->payment?->status?->name) {
                                        'Paid' => 'success',
                                        'Pending' => 'warning',
                                        'Cancelled' => 'danger',
                                        default => 'gray',
                                    })
                                    ->hidden(fn($record) => !$record->payment?->status),
                            ])
                            ->hidden(fn($record) => !$record->payment),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->hidden(fn($record) => !$record->payment),

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
