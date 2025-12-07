<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourtResource\Pages;
use App\Filament\Resources\CourtResource\RelationManagers;
use App\Models\Category;
use App\Models\Court;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CourtResource extends Resource
{
    protected static ?string $model = Court::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    public static function getNavigationLabel(): string
    {
        return __('courts');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('legal_management');
    }

    public static function getPluralModelLabel(): string
    {
        return __('courts');
    }

    public static function getModelLabel(): string
    {
        return __('court');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Section::make(__('court_information'))
                    ->description(__('manage_court_details'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('name'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('location')
                            ->label(__('location'))
                            ->maxLength(255),

                        TextInput::make('court_number')
                            ->label(__('court_number'))
                            ->maxLength(255),

                        Textarea::make('description')
                            ->label(__('description'))
                            ->columnSpanFull(),

                        Select::make('category_id')
                            ->label(__('category'))
                            ->relationship('category', 'name')
                            ->options(Category::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('location')
                    ->label(__('location'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('court_number')
                    ->label(__('court_number'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label(__('category'))
                    ->sortable()
                    ->searchable(),

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
            RelationManagers\CaseRecordsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourts::route('/'),
            'create' => Pages\CreateCourt::route('/create'),
            'edit' => Pages\EditCourt::route('/{record}/edit'),
        ];
    }
}
