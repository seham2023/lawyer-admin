<?php

namespace App\Filament\Resources;

use App\Models\Expense;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use App\Filament\Resources\ExpenseResource\Pages;
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
                TranslatableContainer::make(
                    Forms\Components\Section::make(__('Expense Information'))
                        ->schema([
                            Select::make('category_id')
                                ->label(__('Category'))
                                ->relationship('category', 'name')
                                ->required(),

                            Select::make('status_id')
                                ->label(__('Status'))
                                ->relationship('status', 'name')
                                ->required(),

                            Select::make('currency_id')
                                ->label(__('Currency'))
                                ->relationship('currency', 'name')
                                ->required(),

                            Select::make('pay_method_id')
                                ->label(__('Payment Method'))
                                ->relationship('payMethod', 'name')
                                ->required(),

                            Select::make('payment_id')
                                ->label(__('Payment'))
                                ->relationship('payment', 'name'),

                            TextInput::make('name')
                                ->label(__('Name'))
                                ->required()
                                ->maxLength(255),

                            TextInput::make('receipt_number')
                                ->label(__('Receipt Number'))
                                ->maxLength(255),

                            Textarea::make('reason')
                                ->label(__('Reason'))
                                ->required()
                                ->columnSpanFull(),

                            DatePicker::make('date_time')
                                ->label(__('Date & Time'))
                                ->required(),

                            FileUpload::make('file_path')
                                ->label(__('Receipt File'))
                                ->directory('expenses')
                                ->maxSize(5120) // 5MB
                                ->acceptedFileTypes(['application/pdf', 'image/*']),
                        ])->columns(2),
                )->columnSpanFull(),
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
