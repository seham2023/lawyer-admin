<?php

namespace App\Filament\Resources;

use App\Models\CaseRecord;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Level;
use App\Models\Status;
use App\Models\Client;
use App\Models\Nationality;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use App\Filament\Resources\CaseResource\Pages;
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
                                    ->label(__('client'))
                                    ->options(Client::all()->pluck('name', 'id'))
                                    ->searchable()
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

                                Select::make('level_id')
                                    ->label(__('level'))
                                    ->options(Level::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),

                                Select::make('status_id')
                                    ->label(__('status'))
                                    ->options(Status::where('type', 'case')->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),

                                TextInput::make('court_name')
                                    ->label(__('court_name'))
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('court_number')
                                    ->label(__('court_number'))
                                    ->maxLength(255),

                                TextInput::make('lawyer_name')
                                    ->label(__('lawyer_name'))
                                    ->maxLength(255),

                                TextInput::make('judge_name')
                                    ->label(__('judge_name'))
                                    ->maxLength(255),

                                TextInput::make('location')
                                    ->label(__('location'))
                                    ->maxLength(255),

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

                                Forms\Components\Section::make(__('financial_details'))
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
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('category.name')
                    ->label(__('category'))
                    ->sortable(),

                TextColumn::make('client.name')
                    ->label(__('client'))
                    ->sortable(),

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
        // RelationManagers\PaymentsRelationManager::class,
        RelationManagers\PaymentDetailRelationManager::class,
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
