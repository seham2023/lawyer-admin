<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Category;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\CategoryResource\Pages;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = '';

    public static function getNavigationLabel(): string
    {
        return __('categories');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('system_settings');
    }

    public static function getPluralModelLabel(): string
    {
        return __('categories');
    }

    public static function getModelLabel(): string
    {
        return __('category');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TranslatableContainer::make(
                    TextInput::make('name')
                        ->label(__('Category Name'))
                        ->required()
                        ->maxLength(255)
                        ->columns(2),
                )->columnSpanFull(),

                Textarea::make('description')
                    ->label(__('Description'))
                    ->columnSpanFull(),

                Select::make('type')
                    ->label(__('Type'))
                    ->options([
                        'client' => __('Client'),
                        'case' => __('Case'),
                        'expense' => __('Expense'),
                        'client_type' => __('Client Type'),
                    ])
                    ->required(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('type')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'client' => __('Client'),
                        'case' => __('Case'),
                        'expense' => __('Expense'),
                        'client_type' => __('Client Type'),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
