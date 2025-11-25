<?php

namespace App\Filament\Resources;

use App\Models\Client;
use App\Models\Category;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Qestass\User;
use Illuminate\Database\Eloquent\Builder;

class ClientResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Client Management';

    protected static ?int $navigationSort = -1;

    public static function getNavigationLabel(): string
    {
        return __('Clients');
    }
    public static function getNavigationGroup(): ?string
    {
        return __('client_management');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Clients');
    }
    public static function getModelLabel(): string
    {
        return __('Client');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('type', 'user')
            ->where('parent_id', auth()->id());
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('client_information'))
                    ->schema([
                        // TextInput::make('name')
                        //     ->label(__('name'))
                        //     ->required()
                        //     ->maxLength(255),

                        TextInput::make('first_name')
                            ->label(__('first_name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->label(__('last_name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('email'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label(__('mobile'))
                            ->tel()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('gender')
                            ->label(__('gender'))
                            ->options([
                                'male' => __('male'),
                                'female' => __('female'),
                            ])
                            ->required()
                            ->native(false),
                        TextInput::make('address')
                            ->label(__('address'))
                            ->required()
                            ->maxLength(500),
                        // TextInput::make('company')
                        //     ->label(__('company'))
                        //     ->maxLength(255),

                        // Select::make('category_id')
                        //     ->label(__('category'))
                        //     ->options(Category::where('type', 'client_type')->pluck('name', 'id'))
                        //     ->searchable()
                        //     ->preload()
                        //     ->required()
                        //     ->native(false),

                        Textarea::make('notes')
                            ->label(__('notes'))
                            ->columnSpanFull(),
                    ])->columns(2),

                // Address section using relationship
                //     Forms\Components\Section::make(__('address_information'))
                //         ->relationship('address')
                //         ->schema([
                //             TextInput::make('address')
                //                 ->label(__('address'))
                //                 ->required()
                //                 ->maxLength(500),

                //             TextInput::make('street')
                //                 ->label(__('street'))
                //                 ->maxLength(255),

                //             Select::make('country_id')
                //                 ->label(__('country'))
                //                 ->options(Country::all()->pluck('name', 'id'))
                //                 ->searchable()
                //                 ->preload()
                //                 ->required()
                //                 ->reactive()
                //                 ->afterStateUpdated(function (callable $set) {
                //                     $set('state_id', null);
                //                     $set('city_id', null);
                //                 })
                //                 ->native(false),

                //             Select::make('state_id')
                //                 ->label(__('state'))
                //                 ->options(function (callable $get) {
                //                     $countryId = $get('country_id');
                //                     if (!$countryId) {
                //                         return [];
                //                     }
                //                     return State::where('country_id', $countryId)->pluck('name', 'id');
                //                 })
                //                 ->searchable()
                //                 ->preload()
                //                 ->required()
                //                 ->reactive()
                //                 ->afterStateUpdated(fn(callable $set) => $set('city_id', null))
                //                 ->native(false),

                //             Select::make('city_id')
                //                 ->label(__('city'))
                //                 ->options(function (callable $get) {
                //                     $stateId = $get('state_id');
                //                     if (!$stateId) {
                //                         return [];
                //                     }
                //                     return City::where('state_id', $stateId)->pluck('name', 'id');
                //                 })
                //                 ->searchable()
                //                 ->preload()
                //                 ->required()
                //                 ->native(false),
                //         ])->columns(2),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')
                    ->label(__('first_name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('last_name')
                    ->label(__('last_name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('email')
                    ->label(__('email'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('phone')
                    ->label(__('mobile'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('gender')
                    ->label(__('gender'))
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'male' => 'info',
                        'female' => 'success',
                        default => 'gray',
                    }),

                // TextColumn::make('company')
                //     ->label(__('company'))
                //     ->sortable()
                //     ->searchable()
                //     ->toggleable(),

                TextColumn::make('address.address')
                    ->label(__('address'))
                    ->sortable()
                    ->limit(50)
                    ->toggleable(),

                TextColumn::make('category.name')
                    ->label(__('category'))
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gender')
                    ->label(__('gender'))
                    ->options([
                        'male' => __('male'),
                        'female' => __('female'),
                    ]),

                Tables\Filters\SelectFilter::make('category')
                    ->label(__('category'))
                    ->options(Category::where('type', 'client')->pluck('name', 'id'))
                    ->searchable(),

                Tables\Filters\Filter::make('name')
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label(__('search_by_name')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['name'],
                                fn(Builder $query, $name): Builder => $query->where('name', 'like', "%{$name}%"),
                            );
                    }),

                Tables\Filters\Filter::make('mobile')
                    ->form([
                        Forms\Components\TextInput::make('mobile')
                            ->label(__('search_by_mobile')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['mobile'],
                                fn(Builder $query, $mobile): Builder => $query->where('mobile', 'like', "%{$mobile}%"),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            // RelationManagers\PaymentsRelationManager::class,
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
