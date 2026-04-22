<?php

namespace App\Filament\Lawyer\Resources;

use App\Filament\Lawyer\Resources\SessionResource\Pages;
use App\Filament\Lawyer\Resources\SessionResource\RelationManagers;
use App\Models\Session;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SessionResource extends Resource
{
    protected static ?string $model = Session::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Session Details'))
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label(__('Title'))
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('details')
                            ->label(__('Details'))
                            ->columnSpanFull(),
                        
                        Forms\Components\DateTimePicker::make('datetime')
                            ->label(__('Date & Time'))
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make(__('Legal Context'))
                    ->schema([
                        Forms\Components\Select::make('case_record_id')
                            ->label(__('Case'))
                            ->relationship('caseRecord', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "Case #{$record->id} - " . ($record->client?->getFilamentName() ?? 'Unknown Client'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('case_number', $state)),

                        Forms\Components\TextInput::make('case_number')
                            ->label(__('Case Number'))
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Select::make('court_id')
                            ->label(__('Court'))
                            ->relationship('court', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('priority')
                            ->label(__('Priority'))
                            ->options([
                                'low' => __('Low'),
                                'normal' => __('Normal'),
                                'high' => __('High'),
                            ])
                            ->default('normal')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make(__('Outcome & Follow-up'))
                    ->schema([
                        Forms\Components\TextInput::make('judge_name')
                            ->label(__('Judge Name'))
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('decision')
                            ->label(__('Decision'))
                            ->columnSpanFull(),
                        
                        Forms\Components\DatePicker::make('next_session_date')
                            ->label(__('Next Session Date')),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('case_number')
                    ->label(__('Case #'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('datetime')
                    ->label(__('Date & Time'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('court.name')
                    ->label(__('Court'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('priority')
                    ->label(__('Priority'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'high' => 'danger',
                        'normal' => 'info',
                        'low' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
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
            'index' => Pages\ListSessions::route('/'),
            'create' => Pages\CreateSession::route('/create'),
            'edit' => Pages\EditSession::route('/{record}/edit'),
        ];
    }
}
