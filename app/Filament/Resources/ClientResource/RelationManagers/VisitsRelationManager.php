<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Models\Visit;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class VisitsRelationManager extends RelationManager
{
    protected static string $relationship = 'visits';

    protected static ?string $recordTitleAttribute = 'purpose';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('visit_date')
                    ->label(__('visit_date'))
                    ->required()
                    ->native(false),

                Forms\Components\TextInput::make('purpose')
                    ->label(__('purpose'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('notes')
                    ->label(__('notes'))
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('purpose')
            ->columns([
                Tables\Columns\TextColumn::make('visit_date')
                    ->label(__('visit_date'))
                    ->dateTime()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('purpose')
                    ->label(__('purpose'))
                    ->sortable()
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('notes')
                    ->label(__('notes'))
                    ->limit(50)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('visit_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label(__('from')),
                        Forms\Components\DatePicker::make('until')
                            ->label(__('until')),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn($query, $date) => $query->whereDate('visit_date', '>=', $date)
                            )
                            ->when(
                                $data['until'],
                                fn($query, $date) => $query->whereDate('visit_date', '<=', $date)
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
