<?php

namespace App\Filament\Resources;

use App\Models\Expense;
use App\Models\Category;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Status;
use App\Models\Currency;
use App\Models\PayMethod;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Financial Management';

    public static function getNavigationLabel(): string
    {
        return __('Expenses');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Expense');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Expense Details'))
                    ->schema([
                        Select::make('category_id')
                            ->label(__('Category'))
                            ->options(Category::where('type', 'expense')->pluck('name', 'id'))
                            ->required(),

                        Select::make('status_id')
                            ->label(__('Expense Status'))
                            ->options(Status::where('type', 'expense')->pluck('name', 'id'))
                            ->required(),

                        Select::make('currency_id')
                            ->label(__('Currency'))
                            ->options(Currency::pluck('name', 'id'))
                            ->required(),

                        Select::make('pay_method_id')
                            ->label(__('Payment Method'))
                            ->options(PayMethod::pluck('name', 'id'))
                            ->required(),

                        TextInput::make('amount')
                            ->label(__('Amount'))
                            ->numeric()
                            ->step(0.01)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $amount = $get('amount') ?? 0;
                                $taxPercent = $get('tax') ?? 0;
                                $taxAmount = ($amount * $taxPercent) / 100;
                                $set('total_after_tax', $amount + $taxAmount);
                            }),

                        TextInput::make('tax')
                            ->label(__('Tax (%)'))
                            ->numeric()
                            ->step(0.01)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $amount = $get('amount') ?? 0;
                                $taxPercent = $get('tax') ?? 0;
                                $taxAmount = ($amount * $taxPercent) / 100;
                                $set('total_after_tax', $amount + $taxAmount);
                            }),

                        TextInput::make('total_after_tax')
                            ->label(__('Total After Tax'))
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required(),

                        TextInput::make('receipt_number')
                            ->label(__('Receipt Number')),

                        DateTimePicker::make('date_time')
                            ->label(__('Date and Time'))
                            ->required(),

                        FileUpload::make('file_path')
                            ->label(__('File Path'))
                            ->directory('expenses'),

                        Textarea::make('reason')
                            ->label(__('Reason'))
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make(__('Check Details'))
                    ->schema([
                        TextInput::make('check_number')
                            ->label(__('Check Number')),

                        TextInput::make('bank_name')
                            ->label(__('Bank Name')),

                        Select::make('check_status_id')
                            ->label(__('Status'))
                            ->options(Status::where('type', 'check')->pluck('name', 'id')),

                        DatePicker::make('clearance_date')
                            ->label(__('Clearance Date')),

                        TextInput::make('deposit_account')
                            ->label(__('Deposit Account')),
                    ])->columns(2)->collapsible(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('category.name')
                    ->label(__('Category'))
                    ->sortable(),

                TextColumn::make('currency.name')
                    ->label(__('Currency'))
                    ->sortable(),

                TextColumn::make('receipt_number')
                    ->label(__('Receipt Number'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('date_time')
                    ->label(__('Date & Time'))
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('status.name')
                    ->label(__('Status'))
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('Created At'))
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
