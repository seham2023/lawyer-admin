<?php

namespace App\Filament\Lawyer\Resources\PaymentResource\RelationManagers;

use Closure;
use Filament\Forms;
use Filament\Tables;
use App\Models\Payment;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\PaymentDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\RelationManagers\RelationManager;

class PaymentDetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'paymentDetails';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Payment Installments');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
                        'final_payment' => __('Final Payment'),
                        'partial_payment' => __('Partial Payment'),
                    ])
                    ->required(),

                Forms\Components\TextInput::make('amount')
                    ->label(__('Amount'))
                    ->numeric()
                    ->required()
                    ->minValue(0.01)
                    ->rules([
                        function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                $payment = $this->getOwnerRecord();
                                if (!$payment) return;
                                
                                $remaining = $payment->remaining_payment;
                                if ($value > $remaining) {
                                    $fail(__('Amount cannot exceed the remaining balance of :amount', ['amount' => $remaining]));
                                }
                            };
                        }
                    ]),

                Forms\Components\DateTimePicker::make('paid_at')
                    ->label(__('Payment Date'))
                    ->default(now())
                    ->required(),

                Forms\Components\Textarea::make('details')
                    ->label(__('Payment Details'))
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Payment Name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_type')
                    ->label(__('Payment Type'))
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'installment' => 'info',
                        'deposit' => 'warning',
                        'final_payment' => 'success',
                        'partial_payment' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match ($state) {
                        'installment' => __('Installment'),
                        'deposit' => __('Deposit'),
                        'final_payment' => __('Final Payment'),
                        'partial_payment' => __('Partial Payment'),
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label(__('Amount'))
                    ->money(fn() => $this->getOwnerRecord()->currency?->code ?? \App\Support\Money::getCurrencyCode())
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label(__('Payment Date'))
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('details')
                    ->label(__('Details'))
                    ->limit(30)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_type')
                    ->label(__('Payment Type'))
                    ->options([
                        'installment' => __('Installment'),
                        'deposit' => __('Deposit'),
                        'final_payment' => __('Final Payment'),
                        'partial_payment' => __('Partial Payment'),
                    ]),

                Tables\Filters\Filter::make('paid_at')
                    ->form([
                        Forms\Components\DatePicker::make('paid_from')
                            ->label(__('Paid From')),
                        Forms\Components\DatePicker::make('paid_until')
                            ->label(__('Paid Until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['paid_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('paid_at', '>=', $date),
                            )
                            ->when(
                                $data['paid_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('paid_at', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function () {
                        // Refresh the parent to update remaining balance
                        $this->ownerRecord->refresh();
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->after(function () {
                        // Refresh the parent to update remaining balance
                        $this->ownerRecord->refresh();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('paid_at', 'desc');
    }
}
