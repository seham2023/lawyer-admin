<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NoteResource\Pages;
use App\Filament\Resources\NoteResource\RelationManagers;
use App\Models\Note;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NoteResource extends Resource
{
    protected static ?string $model = Note::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return __('notes');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('legal_management');
    }

    public static function getPluralModelLabel(): string
    {
        return __('notes');
    }

    public static function getModelLabel(): string
    {
        return __('note');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label(__('title'))
                    ->maxLength(255),
                Forms\Components\Textarea::make('body')
                    ->label(__('details'))
                    ->columnSpanFull(),
                Forms\Components\Radio::make('is_office')
                    ->label(__('type'))
                    ->options([
                        1 => __('team'),
                        0 => __('self'),
                    ])
                    ->required()
                    ->inline(),
                Forms\Components\Radio::make('type')
                    ->label(__('category'))
                    ->options([
                        0 => __('work'),
                        1 => __('social'),
                    ])
                    ->required()
                    ->inline()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('title'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('is_office')
                    ->label(__('type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '1' => __('team'),
                        '0' => __('self'),
                        default => __('unknown')
                    })
                    ->color(fn (string $state): string => match ($state) {
                        '1' => 'success',
                        '0' => 'gray',
                        default => 'danger'
                    }),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('category'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '0' => __('work'),
                        '1' => __('social'),
                        default => __('unknown')
                    })
                    ->color(fn (string $state): string => match ($state) {
                        '0' => 'info',
                        '1' => 'warning',
                        default => 'danger'
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListNotes::route('/'),
            'create' => Pages\CreateNote::route('/create'),
            'edit' => Pages\EditNote::route('/{record}/edit'),
        ];
    }
}
