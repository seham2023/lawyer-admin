<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Level;
use App\Models\Status;
use App\Models\Category;
use App\Models\Currency;
use App\Models\CaseRecord;
use App\Models\Nationality;
use App\Models\Qestass\User;
use Filament\Resources\Resource;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CaseResource\Pages;
use App\Filament\Actions\SendTabbyPaymentLinkAction;
use App\Filament\Resources\CaseResource\RelationManagers;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class CaseResource extends Resource
{
    protected static ?string $model = CaseRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    public static function getNavigationLabel(): string
    {
        return __('cases');
    }
 public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id())->latest();
    }
    public static function getNavigationGroup(): ?string
    {
        return __('legal_management');
    }

    public static function getPluralModelLabel(): string
    {
        return __('cases');
    }

    public static function getModelLabel(): string
    {
        return __('case');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make()
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Wizard\Step::make(__('client_information'))
                            ->schema([
                                Select::make('client_id')
                                    ->label(__('user'))
                                    ->options(User::where('parent_id', auth()->user()->id)->pluck('first_name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('client_type_id')
                                    ->label(__('client_type'))
                                    ->relationship('category', 'name')
                                    ->options(Category::where('type', 'client_type')->pluck('name', 'id'))

                                    ->searchable(),
                            ]),

                        Forms\Components\Wizard\Step::make(__('opponent_information'))
                            ->schema([
                                TextInput::make('opponent_name')
                                    ->label(__('opponent_name'))
                                    ->maxLength(255),

                                TextInput::make('opponent_mobile')
                                    ->label(__('opponent_mobile'))
                                    ->tel()
                                    ->maxLength(255),

                                TextInput::make('opponent_email')
                                    ->label(__('opponent_email'))
                                    ->email()
                                    ->maxLength(255),

                                TextInput::make('opponent_location')
                                    ->label(__('opponent_location'))
                                    ->maxLength(255),

                                Select::make('opponent_nationality_id')
                                    ->label(__('opponent_nationality'))
                                    ->options(Nationality::all()->pluck('name', 'id'))
                                    ->searchable(),
                            ]),

                        Forms\Components\Wizard\Step::make(__('opponent_lawyer'))
                            ->schema([
                                TextInput::make('opponent_lawyer_name')
                                    ->label(__('lawyer_name'))
                                    ->maxLength(255),

                                TextInput::make('opponent_lawyer_mobile')
                                    ->label(__('lawyer_mobile'))
                                    ->tel()
                                    ->maxLength(255),

                                TextInput::make('opponent_lawyer_email')
                                    ->label(__('lawyer_email'))
                                    ->email()
                                    ->maxLength(255),
                            ]),

                        Forms\Components\Wizard\Step::make(__('case_details'))
                            ->schema([
                                DatePicker::make('start_date')
                                    ->label(__('start_date'))
                                    ->required(),

                                Select::make('category_id')
                                    ->label(__('category'))
                                    ->options(Category::where('type', 'case')->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),

                                // Select::make('level_id')
                                //     ->label(__('level'))
                                //     ->options(Level::all()->pluck('name', 'id'))
                                //     ->searchable()
                                //     ->required(),

                                Select::make('status_id')
                                    ->label(__('status'))
                                    ->options(Status::where('type', 'case')->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),

                                // Select::make('court_id')
                                //     ->label(__('court'))
                                //     ->options(\App\Models\Court::all()->pluck('name', 'id'))->dehydrated(false)
                                //     ->searchable()
                                //     ->preload()
                                //     ->createOptionForm([
                                //         Forms\Components\TextInput::make('name')
                                //             ->label(__('name'))
                                //             ->required()
                                //             ->maxLength(255),
                                //         Forms\Components\TextInput::make('location')
                                //             ->label(__('location'))
                                //             ->maxLength(255),
                                //         Forms\Components\TextInput::make('court_number')
                                //             ->label(__('court_number'))
                                //             ->maxLength(255),
                                //         Forms\Components\Select::make('category_id')
                                //             ->label(__('category'))
                                //             ->relationship('category', 'name')
                                //             ->options(\App\Models\Category::where('type', 'court')->pluck('name', 'id'))
                                //             ->searchable()
                                //             ->preload(),
                                //         Hidden::make('user_id')
                                //             ->default(auth()->user()->id),
                                //     ])
                                //     ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                                //         return $action
                                //             ->modalHeading(__('Create Court'))
                                //             ->modalSubmitActionLabel(__('Create'))
                                //             ->modalWidth('lg');
                                //     }),





                                TextInput::make('subject')
                                    ->label(__('subject'))
                                    ->required()
                                    ->maxLength(255),

                                Textarea::make('subject_description')
                                    ->label(__('subject_description'))
                                    ->columnSpanFull(),

                                RichEditor::make('notes')
                                    ->label(__('notes'))
                                    ->columnSpanFull(),

                                RichEditor::make('contract')
                                    ->label(__('contract'))
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Wizard\Step::make(__('financial_details'))
                            ->schema([
                                Select::make('currency_id')
                                    ->label(__('currency'))
                                    ->options(Currency::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),

                                TextInput::make('amount')
                                    ->label(__('amount'))
                                    ->numeric()
                                    ->required()
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
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $amount = $get('amount') ?? 0;
                                        $tax = $state ?? 0;
                                        $total = $amount + ($amount * $tax / 100);
                                        $set('total_after_tax', $total);
                                    }),

                                TextInput::make('total_after_tax')
                                    ->label(__('total_after_tax'))
                                    ->numeric()
                                    ->disabled(),

                                Select::make('pay_method_id')
                                    ->label(__('payment_method'))
                                    ->options(\App\Models\PayMethod::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->preload(),

                                Select::make('payment_status_id')
                                    ->label(__('payment_status'))
                                    ->options(Status::where('type', 'payment')->pluck('name', 'id'))
                                    ->searchable()
                                    ->default(1), // Default to 'Pending'
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
                TextColumn::make('category.name')
                    ->label(__('category'))
                    ->sortable(),

                TextColumn::make('client_name')
                    ->label(__('client'))
                    ->getStateUsing(fn($record) => $record->client?->getFilamentName() ?? '-')
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('client', function ($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function ($query, $direction) {
                        return $query->join('users', 'case_records.client_id', '=', 'users.id')
                            ->orderBy('users.first_name', $direction);
                    }),

                TextColumn::make('subject')
                    ->label(__('subject'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('court_name')
                    ->label(__('court_name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('start_date')
                    ->label(__('start_date'))
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
                // SendTabbyPaymentLinkAction::make(),
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
            RelationManagers\SessionsRelationManager::class,
            RelationManagers\DocumentsRelationManager::class,
            RelationManagers\CourtHistoryRelationManager::class,
            RelationManagers\AuditsRelationManager::class,
            // RelationManagers\PaymentsRelationManager::class,
            RelationManagers\PaymentDetailRelationManager::class,
            RelationManagers\PaymentSessionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCases::route('/'),
            'create' => Pages\CreateCase::route('/create'),
            'view' => Pages\ViewCase::route('/{record}'),
            'edit' => Pages\EditCase::route('/{record}/edit'),
        ];
    }


    public function getTabs(): array
    {
        return [
            'details' => __('Details'),
            'sessions' => __('sessions'),
            'documents' => __('Documents'),
            'payments' => __('Payments'),
        ];
    }
}
