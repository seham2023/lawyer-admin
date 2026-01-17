<?php

namespace App\Filament\Resources\ReminderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LogsRelationManager extends RelationManager
{
    protected static string $relationship = 'logs';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('reminders.logs');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('channel')
                    ->required()
                    ->maxLength(50),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('channel')
            ->columns([
                Tables\Columns\TextColumn::make('channel')
                    ->label(__('Channel'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'email' => 'info',
                        'sms' => 'warning',
                        'push' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('sent_at')
                    ->label(__('Sent At'))
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('response')
                    ->label(__('Response'))
                    ->limit(50)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('error_message')
                    ->label(__('Error Message'))
                    ->limit(50)
                    ->toggleable()
                    ->color('danger'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'success' => __('Success'),
                        'failed' => __('Failed'),
                    ]),

                Tables\Filters\SelectFilter::make('channel')
                    ->options([
                        'email' => __('settings.email_channel'),
                        'sms' => __('settings.sms_channel'),
                        'push' => __('settings.push_channel'),
                    ]),
            ])
            ->headerActions([
                // Logs are created automatically, no manual creation
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sent_at', 'desc');
    }
}
