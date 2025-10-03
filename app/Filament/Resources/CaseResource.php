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
                Forms\Components\Wizard::make()
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Wizard\Step::make(__('Client Information'))
                            ->schema([
                                Select::make('client_id')
                                    ->label(__('Client'))
                                    ->options(Client::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),

                                Select::make('client_type_id')
                                    ->label(__('Client Type'))
                                    ->relationship('category', 'name')
                                    ->options(Category::where('type', 'client_type')->pluck('name', 'id'))

                                    ->searchable(),
                            ]),

                        Forms\Components\Wizard\Step::make(__('Opponent Information'))
                            ->schema([
                                TextInput::make('opponent_name')
                                    ->label(__('Opponent Name'))
                                    ->maxLength(255),

                                TextInput::make('opponent_mobile')
                                    ->label(__('Opponent Mobile'))
                                    ->tel()
                                    ->maxLength(255),

                                TextInput::make('opponent_email')
                                    ->label(__('Opponent Email'))
                                    ->email()
                                    ->maxLength(255),

                                TextInput::make('opponent_location')
                                    ->label(__('Opponent Location'))
                                    ->maxLength(255),

                                Select::make('opponent_nationality_id')
                                    ->label(__('Opponent Nationality'))
                                    ->options(Nationality::all()->pluck('name', 'id'))
                                    ->searchable(),
                            ]),

                        Forms\Components\Wizard\Step::make(__('Opponent Lawyer'))
                            ->schema([
                                TextInput::make('opponent_lawyer_name')
                                    ->label(__('Lawyer Name'))
                                    ->maxLength(255),

                                TextInput::make('opponent_lawyer_mobile')
                                    ->label(__('Lawyer Mobile'))
                                    ->tel()
                                    ->maxLength(255),

                                TextInput::make('opponent_lawyer_email')
                                    ->label(__('Lawyer Email'))
                                    ->email()
                                    ->maxLength(255),
                            ]),

                        Forms\Components\Wizard\Step::make(__('Case Details'))
                            ->schema([
                                DatePicker::make('start_date')
                                    ->label(__('Start Date'))
                                    ->required(),

                                Select::make('category_id')
                                    ->label(__('Category'))
                                    ->options(Category::where('type', 'case')->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),

                                Select::make('level_id')
                                    ->label(__('Level'))
                                    ->options(Level::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),

                                Select::make('status_id')
                                    ->label(__('Status'))
                                    ->options(Status::all()->pluck('name', 'id'))
                                    ->searchable()
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

                                RichEditor::make('notes')
                                    ->label(__('Notes'))
                                    ->columnSpanFull(),

                                RichEditor::make('contract')
                                    ->label(__('Contract'))
                                    ->columnSpanFull(),

                                Forms\Components\Section::make(__('Financial Details'))
                                    ->schema([
                                        Select::make('currency_id')
                                            ->label(__('Currency'))
                                            ->options(Currency::all()->pluck('name', 'id'))
                                            ->searchable()
                                            ->required(),

                                        TextInput::make('amount')
                                            ->label(__('Amount'))
                                            ->numeric()
                                            ->required(),

                                        TextInput::make('tax')
                                            ->label(__('Tax (%)'))
                                            ->numeric(),

                                        TextInput::make('total_after_tax')
                                            ->label(__('Total After Tax'))
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
}
