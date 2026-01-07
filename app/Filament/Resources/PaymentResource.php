<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Visit;
use App\Models\Status;
use App\Models\Payment;
use App\Models\Currency;
use App\Models\PayMethod;
use App\Models\CaseRecord;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\BadgeColumn;
use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers\PaymentDetailsRelationManager;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Financial Management';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('Payments');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Payments');
    }

    public static function getModelLabel(): string
    {
        return __('Payment');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Payment Information'))
                    ->schema([
                        Select::make('payable_type')
                            ->label(__('Payment For'))
                            ->options([
                                'App\\Models\\CaseRecord' => __('Case'),
                                'App\\Models\\Visit' => __('Visit'),
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set) {
                                $set('payable_id', null);
                            }),

                        Select::make('payable_id')
                            ->label(__('Select Record'))
                            ->options(function (callable $get) {
                                $type = $get('payable_type');
                                if (!$type) {
                                    return [];
                                }

                                if ($type === 'App\\Models\\CaseRecord') {
                                    return CaseRecord::where('user_id', auth()->id())
                                        ->pluck('subject', 'id');
                                } elseif ($type === 'App\\Models\\Visit') {
                                    return Visit::where('user_id', auth()->id())
                                        ->get()
                                        ->pluck('purpose', 'id');
                                }

                                return [];
                            })
                            ->searchable()
                            ->required()
                            ->hidden(fn(callable $get) => !$get('payable_type')),

                        Select::make('currency_id')
                            ->label(__('Currency'))
                            ->options(Currency::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->default(fn() => Currency::first()?->id),

                        TextInput::make('amount')
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

                        TextInput::make('tax')
                            ->label(__('Tax (%)'))
                            ->numeric()
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $amount = $get('amount') ?? 0;
                                $tax = $state ?? 0;
                                $total = $amount + ($amount * $tax / 100);
                                $set('total_after_tax', $total);
                            }),

                        TextInput::make('total_after_tax')
                            ->label(__('Total After Tax'))
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),

                        Select::make('pay_method_id')
                            ->label(__('Payment Method'))
                            ->options(PayMethod::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        Select::make('status_id')
                            ->label(__('Payment Status'))
                            ->options(Status::where('type', 'payment')->pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),

                        Forms\Components\FileUpload::make('image')
                            ->label(__('Payment Receipt'))
                            ->image()
                            ->directory('payment-receipts')
                            ->nullable(),
                    ])->columns(2),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('payable_type')
                    ->label(__('Type'))
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
                    })
                    ->sortable()
                    ->searchable(),

                TextColumn::make('payable.subject')
                    ->label(__('Related To'))
                    ->getStateUsing(function ($record) {
                        if ($record->payable_type === 'App\\Models\\CaseRecord') {
                            return $record->payable?->subject ?? '-';
                        } elseif ($record->payable_type === 'App\\Models\\Visit') {
                            return $record->payable?->purpose ?? '-';
                        }
                        return '-';
                    })
                    ->searchable()
                    ->limit(30),

                TextColumn::make('amount')
                    ->label(__('Total Amount'))
                    ->money(fn($record) => $record->currency?->code ?? 'USD')
                    ->sortable(),

                TextColumn::make('total_paid')
                    ->label(__('Paid'))
                    ->money(fn($record) => $record->currency?->code ?? 'USD')
                    ->color('success')
                    ->sortable(),

                TextColumn::make('remaining_payment')
                    ->label(__('Remaining'))
                    ->money(fn($record) => $record->currency?->code ?? 'USD')
                    ->color(fn($record) => $record->remaining_payment > 0 ? 'danger' : 'success')
                    ->sortable(),

                TextColumn::make('currency.name')
                    ->label(__('Currency'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('payMethod.name')
                    ->label(__('Payment Method'))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status.name')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn($record) => match ($record->status?->name) {
                        'Paid' => 'success',
                        'Pending' => 'warning',
                        'Cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payable_type')
                    ->label(__('Type'))
                    ->options([
                        'App\\Models\\CaseRecord' => __('Case'),
                        'App\\Models\\Visit' => __('Visit'),
                    ]),

                Tables\Filters\SelectFilter::make('status_id')
                    ->label(__('Status'))
                    ->relationship('status', 'name'),

                Tables\Filters\Filter::make('unpaid')
                    ->label(__('Unpaid'))
                    ->query(fn($query) => $query->whereRaw('amount > (SELECT COALESCE(SUM(amount), 0) FROM payment_details WHERE payment_id = payments.id)')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            PaymentDetailsRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id())
            ->with(['payable', 'currency', 'payMethod', 'status', 'paymentDetails']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'view' => Pages\ViewPayment::route('/{record}'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
