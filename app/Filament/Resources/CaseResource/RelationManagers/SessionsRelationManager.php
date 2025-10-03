<?php

namespace App\Filament\Resources\CaseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SessionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sessions';

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
                    ->required(),


            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('case_record_id')
            ->columns([
                Tables\Columns\TextColumn::make('case_number')
                    ->label(__('case_number')),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('title')),
                Tables\Columns\TextColumn::make('details')
                    ->label(__('details')),
                Tables\Columns\TextColumn::make('datetime')
                    ->label(__('datetime')),
                Tables\Columns\TextColumn::make('priority')
                    ->label(__('priority')),
            ])
            ->filters([
                //
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
            ]);
    }
}
