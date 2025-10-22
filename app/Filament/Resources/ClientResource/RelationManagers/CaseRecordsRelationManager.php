<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Models\CaseRecord;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class CaseRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'caseRecords';

    protected static ?string $recordTitleAttribute = 'subject';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->label(__('category'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                
                Forms\Components\Select::make('status_id')
                    ->label(__('status'))
                    ->relationship('status', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                
                Forms\Components\DatePicker::make('start_date')
                    ->label(__('start_date'))
                    ->required(),
                
                Forms\Components\TextInput::make('court_name')
                    ->label(__('court_name'))
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('subject')
                    ->label(__('subject'))
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\Textarea::make('subject_description')
                    ->label(__('subject_description'))
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('subject')
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('category'))
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('subject')
                    ->label(__('subject'))
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('court_name')
                    ->label(__('court_name'))
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('start_date')
                    ->label(__('start_date'))
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('status.name')
                    ->label(__('status'))
                    ->badge()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_id')
                    ->relationship('status', 'name')
                    ->label(__('status')),
                
                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label(__('category')),
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(), // Disabled as requested
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
}
