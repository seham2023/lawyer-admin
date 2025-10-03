<?php

namespace App\Filament\Resources\CaseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('name'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
               
                Forms\Components\Textarea::make('description')
                    ->label(__('description'))
                    ->required()
                    ->columnSpanFull(),
                     Forms\Components\FileUpload::make('file_path')
                    ->label(__('file_path'))
                    ->required()
                    ->directory('case-documents')->columnSpanFull()
                    ,
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('case_record_id')
            ->columns([
                Tables\Columns\TextColumn::make('case_record_id')
                    ->label(__('case_record_id')),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('name')),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('description')),
                Tables\Columns\TextColumn::make('file_path')
                    ->label(__('file_path')),
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
