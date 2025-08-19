<?php

namespace App\Filament\Resources;

use App\Models\CaseRecord;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use App\Filament\Resources\CaseResource\Pages;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class CaseResource extends Resource
{
    protected static ?string $model = CaseRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'Case Management';

    public static function getNavigationLabel(): string
    {
        return __('Cases');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Case');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TranslatableContainer::make(
                    Forms\Components\Section::make(__('Case Information'))
                        ->schema([
                            Select::make('category_id')
                                ->label(__('Category'))
                                ->relationship('category', 'name')
                                ->required(),

                            Select::make('status_id')
                                ->label(__('Status'))
                                ->relationship('status', 'name')
                                ->required(),

                            Select::make('client_id')
                                ->label(__('Client'))
                                ->relationship('client', 'name')
                                ->required(),

                            Select::make('opponent_id')
                                ->label(__('Opponent'))
                                ->relationship('opponent', 'name'),

                            Select::make('opponent_lawyer_id')
                                ->label(__('Opponent Lawyer'))
                                ->relationship('opponent_lawyer', 'name'),

                            Select::make('level_id')
                                ->label(__('Level'))
                                ->relationship('level', 'name')
                                ->required(),

                            Select::make('client_type_id')
                                ->label(__('Client Type'))
                                ->relationship('client_type', 'name'),

                            DatePicker::make('start_date')
                                ->label(__('Start Date'))
                                ->required(),

                            TextInput::make('court_name')
                                ->label(__('Court Name'))
                                ->required()
                                ->maxLength(255),

                            TextInput::make('court_number')
                                ->label(__('Court Number'))
                                ->maxLength(255),

                            TextInput::make('lawyer_name')
                                ->label(__('Lawyer Name'))
                                ->maxLength(255),

                            TextInput::make('judge_name')
                                ->label(__('Judge Name'))
                                ->maxLength(255),

                            TextInput::make('location')
                                ->label(__('Location'))
                                ->maxLength(255),

                            TextInput::make('subject')
                                ->label(__('Subject'))
                                ->required()
                                ->maxLength(255),

                            Textarea::make('subject_description')
                                ->label(__('Subject Description'))
                                ->columnSpanFull(),

                            Textarea::make('notes')
                                ->label(__('Notes'))
                                ->columnSpanFull(),

                            TextInput::make('contract')
                                ->label(__('Contract'))
                                ->maxLength(255),
                        ])->columns(2),
                )->columnSpanFull(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('category.name')
                    ->label(__('Category'))
                    ->sortable(),

                TextColumn::make('client.name')
                    ->label(__('Client'))
                    ->sortable(),

                TextColumn::make('subject')
                    ->label(__('Subject'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('court_name')
                    ->label(__('Court Name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('start_date')
                    ->label(__('Start Date'))
                    ->date()
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
            'index' => Pages\ListCases::route('/'),
            'create' => Pages\CreateCase::route('/create'),
            'edit' => Pages\EditCase::route('/{record}/edit'),
        ];
    }
}
