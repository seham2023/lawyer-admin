<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Court;
use App\Models\Category;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CourtResource\Pages;
use App\Filament\Resources\CourtResource\RelationManagers;

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
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('location')
                            ->label(__('location'))
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                Select::make('category_id')
                                    ->label(__('category'))
                                    ->relationship('category', 'name')
                                    ->options(Category::where('type', 'court')->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                TextInput::make('court_number')
                                    ->label(__('court_number'))
                                    ->maxLength(255),
                            ]),

                        Textarea::make('description')
                            ->label(__('description'))
                            ->columnSpanFull(),

                      Hidden::make('user_id')
                    ->default(auth()->id())

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
           // RelationManagers\CaseRecordsRelationManager::class,
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id());
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
