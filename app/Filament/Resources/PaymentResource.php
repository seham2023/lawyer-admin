<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Payment;
use App\Models\Currency;
use App\Models\CaseRecord;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\CaseResource\RelationManagers\PaymentDetailRelationManager;

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
                Forms\Components\Section::make(__('payment_information'))
                    ->schema([
                        Select::make('user_id')
                            ->label(__('user'))
                            ->options(User::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set) {
                                $set('case_record_id', null);
                            }),

                        Select::make('case_record_id')
                            ->label(__('case'))
                            ->options(function (callable $get) {
                                $userId = $get('user_id');
                                if (!$userId) {
                                    return [];
                                }
                                return CaseRecord::where('user_id', $userId)->pluck('subject', 'id');
                            })
                            ->searchable()
                            ->required(),

                        Select::make('currency_id')
                            ->label(__('currency'))
                            ->options(Currency::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        TextInput::make('amount')
                            ->label(__('amount'))
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
                            ->label(__('tax'))
                            ->numeric()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $amount = $get('amount') ?? 0;
                                $tax = $state ?? 0;
                                $total = $amount + ($amount * $tax / 100);
                                $set('total_after_tax', $total);
                            }),

                        TextInput::make('total_after_tax')
                            ->label(__('total_after_tax'))
                            ->numeric()
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('case.user.name')
                    ->label(__('user'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('case.subject')
                    ->label(__('case'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('amount')
                    ->label(__('amount'))
                    ->sortable()
                // ->money(fn ($record) => $record->currency ? $record->currency->name : 'USD')
                ,

                TextColumn::make('total_paid')
                    ->label(__('total_paid'))
                    ->sortable()
                // ->money(fn ($record) => $record->currency ? $record->currency->name : 'USD')
                ,

                TextColumn::make('remaining_payment')
                    ->label(__('remaining'))
                    ->sortable()
                // ->money(fn ($record) => $record->currency ? $record->currency->name : 'USD')
                ,

                // TextColumn::make('paymentDetails_count')
                //     ->label(__('payment_details_count'))
                //     ->counts('paymentDetails')
                //     ->sortable()


                TextColumn::make('currency.name')
                    ->label(__('currency'))
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [

            PaymentDetailRelationManager::class,
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id())
            ->orWhereHas('case', function ($query) {
                $query->where('user_id', auth()->id());
            });
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
