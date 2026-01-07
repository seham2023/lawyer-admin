<?php

namespace App\Filament\Resources\CaseResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class SessionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sessions';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('sessions');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('case_number')
                    ->label(__('case_number'))
                    ->maxLength(255),

                Forms\Components\TextInput::make('title')
                    ->label(__('title'))
                    ->maxLength(255),

                Forms\Components\Textarea::make('details')
                    ->label(__('details'))
                    ->columnSpanFull(),

                Forms\Components\DateTimePicker::make('datetime')
                    ->label(__('datetime')),

                Forms\Components\Select::make('priority')
                    ->label(__('priority'))
                    ->options([
                        'low' => __('priority_low'),
                        'medium' => __('priority_medium'),
                        'high' => __('priority_high'),
                    ])
                    ->default('medium')
                    ->required(),

                Forms\Components\Select::make('court_id')
                    ->label(__('court'))
                    ->relationship('court', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\TextInput::make('judge_name')
                    ->label(__('judge_name'))
                    ->maxLength(255),

                Forms\Components\TextInput::make('decision')
                    ->label(__('decision'))
                    ->maxLength(255),

                Forms\Components\DatePicker::make('next_session_date')
                    ->label(__('next_session_date')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('case_number')
                    ->label(__('case_number'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('title'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('datetime')
                    ->label(__('datetime'))
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('priority')
                    ->label(__('priority'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'low' => 'success',
                        'medium' => 'warning',
                        'high' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('court.name')
                    ->label(__('court'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('judge_name')
                    ->label(__('judge_name'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('decision')
                    ->label(__('decision'))
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('next_session_date')
                    ->label(__('next_session_date'))
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => __('priority_low'),
                        'medium' => __('priority_medium'),
                        'high' => __('priority_high'),
                    ]),

                Tables\Filters\SelectFilter::make('court_id')
                    ->label(__('court'))
                    ->relationship('court', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('datetime', 'desc');
    }
}
