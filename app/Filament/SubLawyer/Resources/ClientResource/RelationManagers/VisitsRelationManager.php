<?php

namespace App\Filament\SubLawyer\Resources\ClientResource\RelationManagers;

use App\Models\Status;
use App\Models\Visit;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VisitsRelationManager extends RelationManager
{
    protected static string $relationship = 'visits';
    
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id());
    }

    protected static ?string $recordTitleAttribute = 'purpose';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('Visits');
    }

    public static function getModelLabel(): string
    {
        return __('Visit');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Visits');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('visit_date')
                    ->label(__('visit_date'))
                    ->required()
                    ->native(false),

                Forms\Components\TextInput::make('purpose')
                    ->label(__('purpose'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('status_id')
                    ->label(__('status'))
                    ->options(Status::where('type', 'visit')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false),
                     Forms\Components\Select::make('services')
                            ->multiple()
                            ->relationship('services', 'name')
                            ->searchable()
                            ->preload()
                            ->label(__('Services'))
                            ->columnSpanFull(),

                Forms\Components\Textarea::make('notes')
                    ->label(__('notes'))
                    ->columnSpanFull(),
                Hidden::make('user_id')
                    ->default(auth()->user()->id),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('purpose')
            ->columns([
                Tables\Columns\TextColumn::make('visit_date')
                    ->label(__('visit_date'))
                    ->dateTime()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('purpose')
                    ->label(__('purpose'))
                    ->sortable()
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('payment.amount')
                    ->label(__('Total Amount'))
                    ->money(fn() => \App\Support\Money::getCurrencyCode())
                    ->default('-')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('payment.total_paid')
                    ->label(__('Paid'))
                    ->money(fn() => \App\Support\Money::getCurrencyCode())
                    ->default('-')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('payment.remaining_payment')
                    ->label(__('Remaining'))
                    ->money(fn() => \App\Support\Money::getCurrencyCode())
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

                Tables\Columns\TextColumn::make('notes')
                    ->label(__('notes'))
                    ->limit(50)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('visit_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label(__('from')),
                        Forms\Components\DatePicker::make('until')
                            ->label(__('until')),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn($query, $date) => $query->whereDate('visit_date', '>=', $date)
                            )
                            ->when(
                                $data['until'],
                                fn($query, $date) => $query->whereDate('visit_date', '<=', $date)
                            );
                    }),

                Tables\Filters\SelectFilter::make('status_id')
                    ->label(__('Status'))
                    ->options(\App\Models\Status::where('type', 'visit')->pluck('name', 'id')),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label(__('Payment Status'))
                    ->options([
                        'paid' => __('Paid'),
                        'partial' => __('Partial'),
                        'unpaid' => __('Unpaid'),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data) {
                        return $query->when($data['value'] === 'paid', function ($query) {
                            return $query->whereHas('payment', function ($q) {
                                $q->whereRaw('amount <= (SELECT COALESCE(SUM(amount), 0) FROM payment_details WHERE payment_id = payments.id)');
                            });
                        })->when($data['value'] === 'partial', function ($query) {
                            return $query->whereHas('payment', function ($q) {
                                $q->whereRaw('amount > (SELECT COALESCE(SUM(amount), 0) FROM payment_details WHERE payment_id = payments.id)')
                                  ->whereRaw('(SELECT COALESCE(SUM(amount), 0) FROM payment_details WHERE payment_id = payments.id) > 0');
                            });
                        })->when($data['value'] === 'unpaid', function ($query) {
                            return $query->whereDoesntHave('payment')
                                ->orWhereHas('payment', function ($q) {
                                    $q->whereRaw('(SELECT COALESCE(SUM(amount), 0) FROM payment_details WHERE payment_id = payments.id) = 0');
                                });
                        });
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn($record) => \App\Filament\SubLawyer\Resources\VisitResource::getUrl('view', ['record' => $record])),
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
                            ->prefix(fn() => \App\Support\Money::getCurrencySymbol())
                            ->minValue(0.01)
                            ->rules([
                                function ($record) {
                                    return function (string $attribute, $value, \Closure $fail) use ($record) {
                                        if (!$record->payment) return;
                                        
                                        $remaining = $record->payment->remaining_payment;
                                        if ($value > $remaining) {
                                            $fail(__('Amount cannot exceed the remaining balance of :amount', ['amount' => $remaining]));
                                        }
                                    };
                                }
                            ])
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
                        // Set payment_id from the visit's payment
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
