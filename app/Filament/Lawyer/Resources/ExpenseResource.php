<?php

namespace App\Filament\Lawyer\Resources;

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
use App\Filament\Lawyer\Resources\ExpenseResource\Pages;
use App\Filament\Lawyer\Resources\ExpenseResource\RelationManagers;
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
                            ->default(fn() => Category::where('type', 'expense')->first()?->id)
                            ->required(),

                        Select::make('status_id')
                            ->label(__('expense_status'))
                            ->options(Status::where('type', 'expense')->pluck('name', 'id'))
                            ->default(fn() => Status::where('type', 'expense')->first()?->id)
                            ->required(),

                        TextInput::make('name')
                            ->label(__('name'))
                            ->required(),

                        TextInput::make('receipt_number')
                            ->label(__('receipt_number')),

                        DatePicker::make('date')
                            ->label(__('date'))
                            ->required(),

                        FileUpload::make('file_path')
                            ->label(__('file_path'))
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(10240)
                            ->directory('expenses'),

                        Textarea::make('description')
                            ->label(__('description'))
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make(__('financial_details'))
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Select::make('currency_id')
                                    ->label(__('currency'))
                                    ->options(Currency::all()->pluck('name', 'id'))
                                    ->default(fn() => Currency::first()?->id)
                                    ->searchable()
                                    ->required()
                                    ->prefixIcon('heroicon-o-currency-dollar'),

                                TextInput::make('amount')
                                    ->label(__('amount'))
                                    ->numeric()
                                    ->required()
                                    ->prefixIcon('heroicon-o-banknotes')
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
                                    ->prefixIcon('heroicon-o-receipt-percent')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $amount = $get('amount') ?? 0;
                                        $tax = $state ?? 0;
                                        $total = $amount + ($amount * $tax / 100);
                                        $set('total_after_tax', $total);
                                    }),
                            ]),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                TextInput::make('total_after_tax')
                                    ->label(__('total_after_tax'))
                                    ->numeric()
                                    ->disabled()
                                    ->prefixIcon('heroicon-o-calculator'),

                                Select::make('pay_method_id')
                                    ->label(__('payment_method'))
                                    ->options(PayMethod::all()->pluck('name', 'id'))
                                    ->default(fn() => PayMethod::first()?->id)
                                    ->searchable()
                                    ->required()
                                    ->preload()
                                    ->prefixIcon('heroicon-o-credit-card'),

                                Select::make('payment_status_id')
                                    ->label(__('payment_status'))
                                    ->options(Status::where('type', 'payment')->pluck('name', 'id'))
                                    ->searchable()
                                    ->default(1)
                                    ->prefixIcon('heroicon-o-information-circle'),
                            ]),
                    ]),

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

                TextColumn::make('receipt_number')
                    ->label(__('receipt_number'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('date')
                    ->label(__('date'))
                    ->date()
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
            RelationManagers\PaymentsRelationManager::class,
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
