<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CaseRecordResource\Pages;
use App\Filament\Resources\CaseRecordResource\RelationManagers;
use App\Models\CaseRecord;
use App\Models\Category;
use App\Models\Status;
use App\Models\Level;
use App\Models\Payment;
use App\Models\Client;
use App\Models\Opponent;
use App\Models\OpponentLawyer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Wizard;

class CaseRecordResource extends Resource
{
    protected static ?string $model = CaseRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';
    
    protected static ?string $navigationGroup = 'Legal Management';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Case Information')
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->label('Client')
                            ->relationship('client', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('client_type_id')
                            ->label('Client Type')
                            ->relationship('client_type', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('status_id')
                            ->label('Status')
                            ->relationship('status', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('level_id')
                            ->label('Level')
                            ->relationship('level', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required()
                            ->displayFormat('d/m/Y'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Opponent Information')
                    ->schema([
                        Forms\Components\Select::make('opponent_id')
                            ->label('Opponent')
                            ->relationship('opponent', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('opponent_lawyer_id')
                            ->label('Opponent Lawyer')
                            ->relationship('opponent_lawyer', 'name')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Court Information')
                    ->schema([
                        Forms\Components\TextInput::make('court_name')
                            ->label('Court Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('court_number')
                            ->label('Court Number')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('lawyer_name')
                            ->label('Lawyer Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('judge_name')
                            ->label('Judge Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('location')
                            ->label('Location')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('payment_id')
                            ->label('Payment')
                            ->relationship('payment', 'id')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Case Details')
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->label('Subject')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('subject_description')
                            ->label('Subject Description')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('contract')
                            ->label('Contract')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('court_name')
                    ->label('Court Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('court_number')
                    ->label('Court Number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lawyer_name')
                    ->label('Lawyer Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('judge_name')
                    ->label('Judge Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status.name')
                    ->label('Status')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('subject')
                    ->label('Subject')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('location')
                    ->label('Location')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('status_id')
                    ->label('Status')
                    ->relationship('status', 'name')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Client')
                    ->relationship('client', 'name')
                    ->searchable(),
                Tables\Filters\Filter::make('start_date')
                    ->form([
                        Forms\Components\DatePicker::make('start_date_from')
                            ->label('Start Date From'),
                        Forms\Components\DatePicker::make('start_date_until')
                            ->label('Start Date Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['start_date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_date', '<=', $date),
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
            ])
            ->defaultSort('start_date', 'desc');
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
            'index' => Pages\ListCaseRecords::route('/'),
            'create' => Pages\CreateCaseRecord::route('/create'),
            'edit' => Pages\EditCaseRecord::route('/{record}/edit'),
        ];
    }
}
