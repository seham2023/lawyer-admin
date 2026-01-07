<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Models\CaseRecord;
use App\Models\Currency;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class CaseRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'caseRecords';

    protected static ?string $recordTitleAttribute = 'subject';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->label(__('category'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('status_id')
                    ->label(__('status'))
                    ->relationship('status', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\DatePicker::make('start_date')
                    ->label(__('start_date'))
                    ->required()
                    ->native(false),

                Forms\Components\TextInput::make('court_name')
                    ->label(__('court_name'))
                    ->maxLength(255),

                Forms\Components\TextInput::make('subject')
                    ->label(__('subject'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('subject_description')
                    ->label(__('subject_description'))
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('subject')
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('category'))
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('subject')
                    ->label(__('subject'))
                    ->sortable()
                    ->searchable()
                    ->limit(40)
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('court_name')
                    ->label(__('court_name'))
                    ->sortable()
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('start_date')
                    ->label(__('start_date'))
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status.name')
                    ->label(__('status'))
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment.amount')
                    ->label(__('Total Amount'))
                    ->money(fn() => Currency::first()->code ?? 'USD')
                    ->default('-')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('payment.total_paid')
                    ->label(__('Paid'))
                    ->money(fn() => Currency::first()->code ?? 'USD')
                    ->default('-')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('payment.remaining_payment')
                    ->label(__('Remaining'))
                    ->money(fn() => Currency::first()->code ?? 'USD')
                    ->default('-')
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'danger' : 'success'),

                Tables\Columns\IconColumn::make('payment_status')
                    ->label(__('Payment Status'))
                    ->getStateUsing(function ($record) {
                        if (!$record->payment) {
                            return 'unpaid';
                        }
                        $remaining = $record->payment->remaining_payment ?? 0;
                        if ($remaining <= 0) {
                            return 'paid';
                        }
                        if ($record->payment->total_paid > 0) {
                            return 'partial';
                        }
                        return 'unpaid';
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'paid' => 'heroicon-o-check-circle',
                        'partial' => 'heroicon-o-clock',
                        'unpaid' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        'unpaid' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_id')
                    ->relationship('status', 'name')
                    ->label(__('status')),

                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label(__('category')),

                Tables\Filters\Filter::make('has_payment')
                    ->label(__('Has Payment'))
                    ->query(fn($query) => $query->whereHas('payment')),

                Tables\Filters\Filter::make('unpaid')
                    ->label(__('Unpaid'))
                    ->query(fn($query) => $query->whereDoesntHave('payment')
                        ->orWhereHas('payment', function ($q) {
                            $q->whereRaw('amount > (SELECT COALESCE(SUM(amount), 0) FROM payment_details WHERE payment_id = payments.id)');
                        })),
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(), // Disabled as requested
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn($record) => \App\Filament\Resources\CaseResource::getUrl('view', ['record' => $record])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('add_payment_detail')
                    ->label(__('Add Payment'))
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn($record) => $record->payment !== null)
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label(__('Payment Name'))
                            ->placeholder(__('e.g., First Installment'))
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('payment_type')
                            ->label(__('Payment Type'))
                            ->options([
                                'installment' => __('Installment'),
                                'deposit' => __('Deposit'),
                                'final' => __('Final Payment'),
                                'partial' => __('Partial Payment'),
                            ])
                            ->required()
                            ->native(false)
                            ->default('installment'),

                        Forms\Components\TextInput::make('amount')
                            ->label(__('Amount'))
                            ->numeric()
                            ->required()
                            ->prefix(fn() => Currency::first()->symbol ?? '$')
                            ->minValue(0.01)
                            ->helperText(fn($record) => __('Remaining balance') . ': ' . ($record->payment->remaining_payment ?? 0)),

                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label(__('Payment Date'))
                            ->required()
                            ->default(now())
                            ->native(false),

                        Forms\Components\Select::make('pay_method_id')
                            ->label(__('Payment Method'))
                            ->options(\App\Models\PayMethod::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->native(false),

                        Forms\Components\Textarea::make('details')
                            ->label(__('Payment Details'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->action(function ($record, array $data) {
                        // Set payment_id from the case's payment
                        $data['payment_id'] = $record->payment->id;

                        $record->payment->paymentDetails()->create($data);

                        \Filament\Notifications\Notification::make()
                            ->title(__('Payment installment added successfully'))
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
