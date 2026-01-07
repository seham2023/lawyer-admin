<?php

namespace App\Filament\Resources\CaseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\CaseRecordAudit;

class AuditsRelationManager extends RelationManager
{
    protected static string $relationship = 'audits';

    protected static ?string $title = 'Audit Trail';

    protected static ?string $recordTitleAttribute = 'action';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('action')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('action')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Who')
                    ->searchable()
                    ->default('System'),

                Tables\Columns\TextColumn::make('event_type')
                    ->label('Event')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        'restored' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('field_label')
                    ->label('Field')
                    ->getStateUsing(fn(CaseRecordAudit $record) => $record->field_label)
                    ->searchable(query: function ($query, $search) {
                        return $query->where('field_name', 'like', "%{$search}%");
                    }),

                Tables\Columns\TextColumn::make('formatted_old_value')
                    ->label('From')
                    ->limit(30)
                    ->tooltip(fn(CaseRecordAudit $record) => $record->formatted_old_value),

                Tables\Columns\TextColumn::make('formatted_new_value')
                    ->label('To')
                    ->limit(30)
                    ->tooltip(fn(CaseRecordAudit $record) => $record->formatted_new_value),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('event_type')
                    ->label('Event Type')
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                        'restored' => 'Restored',
                    ]),

                Tables\Filters\SelectFilter::make('field_name')
                    ->label('Field')
                    ->options([
                        'status_id' => 'Status',
                        'court_id' => 'Court',
                        'subject' => 'Subject',
                        'category_id' => 'Category',
                        'client_id' => 'Client',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('created_at', '<=', $data['until']));
                    }),
            ])
            ->headerActions([
                // No create action - audits are auto-generated
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading('Audit Details')
                    ->form([
                        Forms\Components\TextInput::make('created_at')
                            ->label('When')
                            ->disabled(),
                        Forms\Components\TextInput::make('user.name')
                            ->label('Who')
                            ->disabled(),
                        Forms\Components\TextInput::make('event_type')
                            ->label('Event Type')
                            ->disabled(),
                        Forms\Components\TextInput::make('field_label')
                            ->label('Field')
                            ->disabled(),
                        Forms\Components\Textarea::make('formatted_old_value')
                            ->label('Old Value')
                            ->disabled(),
                        Forms\Components\Textarea::make('formatted_new_value')
                            ->label('New Value')
                            ->disabled(),
                        Forms\Components\TextInput::make('ip_address')
                            ->label('IP Address')
                            ->disabled(),
                    ]),
            ])
            ->bulkActions([
                // No bulk actions for audit trail
            ]);
    }

    public function isReadOnly(): bool
    {
        return true; // Audit trail is read-only
    }
}
