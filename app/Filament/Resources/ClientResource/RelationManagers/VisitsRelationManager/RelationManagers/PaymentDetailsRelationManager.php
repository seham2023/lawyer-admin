<?php

namespace App\Filament\Resources\ClientResource\RelationManagers\VisitsRelationManager\RelationManagers;

use App\Models\PaymentDetail;
use App\Models\PayMethod;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class PaymentDetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'paymentDetails';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Payment Installments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('Payment Name'))
                    ->placeholder(__('e.g., First Installment, Deposit, etc.'))
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
                    ->native(false),

                Forms\Components\TextInput::make('amount')
                    ->label(__('Amount'))
                    ->numeric()
                    ->required()
                    ->prefix(fn() => \App\Models\Currency::first()->symbol ?? '$')
                    ->minValue(0.01),

                Forms\Components\DateTimePicker::make('paid_at')
                    ->label(__('Payment Date'))
                    ->required()
                    ->default(now())
                    ->native(false),

                Forms\Components\Select::make('pay_method_id')
                    ->label(__('Payment Method'))
                    ->options(PayMethod::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->native(false),

                Forms\Components\Textarea::make('details')
                    ->label(__('Payment Details'))
                    ->placeholder(__('Additional notes about this payment...'))
                    ->rows(3)
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
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('payment_type')
                    ->label(__('Type'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'deposit' => 'info',
                        'installment' => 'warning',
                        'final' => 'success',
                        'partial' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'installment' => __('Installment'),
                        'deposit' => __('Deposit'),
                        'final' => __('Final Payment'),
                        'partial' => __('Partial Payment'),
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->label(__('Amount'))
                    ->money(fn() => \App\Models\Currency::first()->code ?? 'USD')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label(__('Payment Date'))
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('payMethod.name')
                    ->label(__('Payment Method'))
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('details')
                    ->label(__('Details'))
                    ->limit(50)
                    ->toggleable()
                    ->wrap(),

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
                        'final' => __('Final Payment'),
                        'partial' => __('Partial Payment'),
                    ]),

                Tables\Filters\SelectFilter::make('pay_method_id')
                    ->label(__('Payment Method'))
                    ->relationship('payMethod', 'name'),

                Tables\Filters\Filter::make('paid_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label(__('From Date')),
                        Forms\Components\DatePicker::make('until')
                            ->label(__('Until Date')),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn($query, $date) => $query->whereDate('paid_at', '>=', $date)
                            )
                            ->when(
                                $data['until'],
                                fn($query, $date) => $query->whereDate('paid_at', '<=', $date)
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Automatically set payment_id from the parent visit's payment
                        $data['payment_id'] = $this->ownerRecord->payment?->id;
                        return $data;
                    })
                    ->before(function (Tables\Actions\CreateAction $action) {
                        // Check if visit has a payment
                        if (!$this->ownerRecord->payment) {
                            \Filament\Notifications\Notification::make()
                                ->title(__('No payment found'))
                                ->body(__('Please create a payment for this visit first.'))
                                ->danger()
                                ->send();

                            $action->halt();
                        }
                    })
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
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(function () {
                            // Refresh the parent to update remaining balance
                            $this->ownerRecord->refresh();
                        }),
                ]),
            ])
            ->emptyStateHeading(__('No payment installments yet'))
            ->emptyStateDescription(__('Add payment installments to track partial payments for this visit.'))
            ->emptyStateIcon('heroicon-o-banknotes');
    }

    // Check if the visit has a payment before showing this relation manager
    public static function canViewForRecord(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->payment !== null;
    }
}
