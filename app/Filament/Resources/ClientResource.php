<?php

namespace App\Filament\Resources;

use App\Models\Client;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use App\Filament\Resources\ClientResource\Pages;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Client Management';

    public static function getNavigationLabel(): string
    {
        return __('Clients');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Client');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TranslatableContainer::make(
                    Forms\Components\Section::make(__('Client Information'))
                        ->schema([
                            TextInput::make('name')
                                ->label(__('Name'))
                                ->required()
                                ->maxLength(255),

                            TextInput::make('email')
                                ->label(__('Email'))
                                ->email()
                                ->required()
                                ->maxLength(255),

                            TextInput::make('phone')
                                ->label(__('Phone'))
                                ->tel()
                                ->required()
                                ->maxLength(255),

                            TextInput::make('address')
                                ->label(__('Address'))
                                ->maxLength(255),

                            Select::make('city_id')
                                ->label(__('City'))
                                ->relationship('city', 'name')
                                ->required(),

                            Select::make('country_id')
                                ->label(__('Country'))
                                ->relationship('country', 'name')
                                ->required(),

                            Select::make('nationality_id')
                                ->label(__('Nationality'))
                                ->relationship('nationality', 'name')
                                ->required(),

                            Textarea::make('notes')
                                ->label(__('Notes'))
                                ->columnSpanFull(),
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

                TextColumn::make('email')
                    ->label(__('Email'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('city.name')
                    ->label(__('City'))
                    ->sortable(),

                TextColumn::make('country.name')
                    ->label(__('Country'))
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
