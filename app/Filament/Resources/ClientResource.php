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
use App\Models\Currency;
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
                // Tables\Actions\Action::make('add_visit')
                //     ->label(__('Add Visit'))
                //     ->icon('heroicon-o-calendar')
                //     ->color('success')
                //     ->form([
                //         Forms\Components\DateTimePicker::make('visit_date')
                //             ->label(__('Visit Date'))
                //             ->required()
                //             ->default(now()),
                //         Forms\Components\TextInput::make('purpose')
                //             ->label(__('Purpose'))
                //             ->required()
                //             ->maxLength(255),
                //         Forms\Components\Textarea::make('notes')
                //             ->label(__('Notes'))
                //             ->rows(3)
                //             ->columnSpanFull(),
                        
                //         Forms\Components\Section::make(__('financial_details'))
                //             ->schema([
                //                 Select::make('currency_id')
                //                     ->label(__('currency'))
                //                     ->options(Currency::all()->pluck('name', 'id')->mapWithKeys(fn($name, $id) => [$id => $name])->toArray())
                //                     ->searchable()
                //                     ->required(),

                //                 TextInput::make('amount')
                //                     ->label(__('amount'))
                //                     ->numeric()
                //                     ->required()
                //                     ->reactive()
                //                     ->afterStateUpdated(function ($state, callable $set, callable $get) {
                //                         $tax = $get('tax') ?? 0;
                //                         $amount = $state ?? 0;
                //                         $total = $amount + ($amount * $tax / 100);
                //                         $set('total_after_tax', $total);
                //                     }),

                //                 TextInput::make('tax')
                //                     ->label(__('tax'))
                //                     ->numeric()
                //                     ->reactive()
                //                     ->afterStateUpdated(function ($state, callable $set, callable $get) {
                //                         $amount = $get('amount') ?? 0;
                //                         $tax = $state ?? 0;
                //                         $total = $amount + ($amount * $tax / 100);
                //                         $set('total_after_tax', $total);
                //                     }),

                //                 TextInput::make('total_after_tax')
                //                     ->label(__('total_after_tax'))
                //                     ->numeric()
                //                     ->disabled(),
                //             ])
                //             ->columns(2)
                //             ->collapsible(),
                //     ])
                //     ->action(function (User $record, array $data) {
                //         // Create payment if financial details are provided
                //         $paymentId = null;
                //         if (isset($data['amount']) && isset($data['currency_id'])) {
                //             $payment = \App\Models\Payment::create([
                //                 'amount' => $data['amount'],
                //                 'tax' => $data['tax'] ?? 0,
                //                 'currency_id' => $data['currency_id'],
                //                 'user_id' => auth()->id(),
                //                 'client_id' => $record->id,
                //                 'payment_date' => now(),
                //             ]);
                            
                //             $paymentId = $payment->id;
                //         }

                //         \App\Models\Visit::create([
                //             'user_id' => auth()->id(),
                //             'client_id' => $record->id,
                //             'visit_date' => $data['visit_date'],
                //             'purpose' => $data['purpose'],
                //             'notes' => $data['notes'] ?? null,
                //         ]);

                //         \Filament\Notifications\Notification::make()
                //             ->title(__('Visit created successfully'))
                //             ->success()
                //             ->send();
                //     }),
                // Tables\Actions\Action::make('add_case')
                //     ->label(__('Add Case'))
                //     ->icon('heroicon-o-briefcase')
                //     ->color('warning')
                //     ->form([
                //         Forms\Components\Select::make('category_id')
                //             ->label(__('Category'))
                //             ->options(\App\Models\Category::where('type', 'case')->pluck('name', 'id'))
                //             ->searchable()
                //             ->required()
                //             ->native(false),
                //         Forms\Components\Select::make('status_id')
                //             ->label(__('Status'))
                //             ->options(\App\Models\Status::pluck('name', 'id'))
                //             ->searchable()
                //             ->required()
                //             ->native(false),
                //         Forms\Components\DatePicker::make('start_date')
                //             ->label(__('Start Date'))
                //             ->required()
                //             ->default(now()),
                //         Forms\Components\TextInput::make('subject')
                //             ->label(__('Subject'))
                //             ->required()
                //             ->maxLength(255)
                //             ->columnSpanFull(),
                //         Forms\Components\Textarea::make('subject_description')
                //             ->label(__('Description'))
                //             ->rows(3)
                //             ->columnSpanFull(),
                //         Forms\Components\TextInput::make('court_name')
                //             ->label(__('Court Name'))
                //             ->maxLength(255),
                //         Forms\Components\TextInput::make('court_number')
                //             ->label(__('Court Number'))
                //             ->maxLength(255),
                //     ])
                //     ->action(function (User $record, array $data) {
                //         \App\Models\CaseRecord::create([
                //             'user_id' => auth()->id(),
                //             'client_id' => $record->id,
                //             'client_type_id' => 3,
                //             'category_id' => $data['category_id'],
                //             'status_id' => $data['status_id'],
                //             'start_date' => $data['start_date'],
                //             'subject' => $data['subject'],
                //             'subject_description' => $data['subject_description'] ?? null,
                //             'court_name' => $data['court_name'] ?? null,
                //             'court_number' => $data['court_number'] ?? null,
                //         ]);

                //         \Filament\Notifications\Notification::make()
                //             ->title(__('Case created successfully'))
                //             ->success()
                //             ->send();
                //     }),
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
            RelationManagers\VisitsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'view' => Pages\ViewClient::route('/{record}'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
