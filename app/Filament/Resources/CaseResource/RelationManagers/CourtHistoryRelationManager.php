<?php

namespace App\Filament\Resources\CaseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Court;

class CourtHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'courtHistory';

    protected static ?string $title = 'Court History';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('court_id')
                    ->label(__('Court'))
                    ->relationship('court', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\DatePicker::make('transfer_date')
                    ->label(__('Transfer Date'))
                    ->required()
                    ->default(now()),

                Forms\Components\TextInput::make('transfer_reason')
                    ->label(__('Transfer Reason'))
                    ->maxLength(255)
                    ->placeholder('e.g., Appeal, Jurisdiction Change, Initial Filing'),

                Forms\Components\Textarea::make('notes')
                    ->label(__('Notes'))
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('is_current')
                    ->label(__('Is Current Court'))
                    ->default(false)
                    ->helperText('Only one court can be marked as current'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('court.name')
            ->columns([
                Tables\Columns\TextColumn::make('court.name')
                    ->label(__('Court'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('transfer_date')
                    ->label(__('Transfer Date'))
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('transfer_reason')
                    ->label(__('Reason'))
                    ->limit(30),

                Tables\Columns\IconColumn::make('is_current')
                    ->label(__('Current'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('transfer_date', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_current')
                    ->label(__('Current Court'))
                    ->placeholder(__('All'))
                    ->trueLabel(__('Current Only'))
                    ->falseLabel(__('Historical Only')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('Transfer to New Court'))
                    ->mutateFormDataUsing(function (array $data): array {
                        // If marking as current, unmark all others
                        if ($data['is_current'] ?? false) {
                            $this->getOwnerRecord()->courtHistory()
                                ->update(['is_current' => false]);
                        }
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // If marking as current, unmark all others
                        if ($data['is_current'] ?? false) {
                            $this->getOwnerRecord()->courtHistory()
                                ->where('id', '!=', $this->record->id)
                                ->update(['is_current' => false]);
                        }
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
