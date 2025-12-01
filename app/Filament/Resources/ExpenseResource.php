<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Status;
use App\Models\Expense;
use App\Models\Category;
use App\Models\Currency;
use App\Models\PayMethod;
use Filament\Resources\Resource;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Resources\ExpenseResource\Pages;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function getNavigationLabel(): string
    {
        return __('expenses');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('financial_management');
    }

    public static function getPluralModelLabel(): string
    {
        return __('expenses');
    }

    public static function getModelLabel(): string
    {
        return __('expense');
    }
   public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id());
    }
    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('expense_details'))
                    ->schema([
                        Select::make('category_id')
                            ->label(__('category'))
                            ->options(Category::where('type', 'expense')->pluck('name', 'id'))
                            ->required(),

                        Select::make('status_id')
                            ->label(__('expense_status'))
                            ->options(Status::where('type', 'expense')->pluck('name', 'id'))
                            ->required(),

                        Select::make('currency_id')
                            ->label(__('currency'))
                            ->options(Currency::pluck('name', 'id'))
                            ->required(),

                        Select::make('pay_method_id')
                            ->label(__('payment_method'))
                            ->options(PayMethod::pluck('name', 'id'))
                            ->required(),

                        TextInput::make('amount')
                            ->label(__('amount'))
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
                            ->label(__('tax'))
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
                            ->label(__('total_after_tax'))
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('name')
                            ->label(__('name'))
                            ->required(),

                        TextInput::make('receipt_number')
                            ->label(__('receipt_number')),

                        DateTimePicker::make('date_time')
                            ->label(__('date_time'))
                            ->required(),

                        FileUpload::make('file_path')
                            ->label(__('file_path'))
                            ->directory('expenses'),

                        Textarea::make('reason')
                            ->label(__('reason'))
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make(__('check_details'))
                    ->schema([
                        TextInput::make('check_number')
                            ->label(__('check_number')),

                        TextInput::make('bank_name')
                            ->label(__('bank_name')),

                        Select::make('check_status_id')
                            ->label(__('status'))
                            ->options(Status::where('type', 'check')->pluck('name', 'id')),

                        DatePicker::make('clearance_date')
                            ->label(__('clearance_date')),

                        TextInput::make('deposit_account')
                            ->label(__('deposit_account')),
                    ])->columns(2)->collapsible()->visible(fn (callable $get) => $get('pay_method_id') === 2), // '2' is the ID for check payment method based on seeder order


                      Hidden::make('user_id')
                    ->default(auth()->id())
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('category.name')
                    ->label(__('category'))
                    ->sortable(),

                TextColumn::make('currency.name')
                    ->label(__('currency'))
                    ->sortable(),

                TextColumn::make('receipt_number')
                    ->label(__('receipt_number'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('date_time')
                    ->label(__('date_time'))
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('status.name')
                    ->label(__('status'))
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
