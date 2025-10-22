<?php

namespace App\Filament\Resources\CaseResource\RelationManagers;

use App\Models\PaymentSession;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class PaymentSessionsRelationManager extends RelationManager
{
    protected static string $relationship = 'paymentSessions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('provider')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('provider')
            ->columns([
                TextColumn::make('provider')
                    ->label('Provider')
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('session_id')
                    ->label('Session ID')
                    ->toggleable()
                    ->searchable(),
                
                TextColumn::make('payment_id')
                    ->label('Payment ID')
                    ->toggleable()
                    ->searchable(),
                
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created', 'link_sent' => 'warning',
                        'authorized', 'closed', 'captured' => 'success',
                        'rejected', 'expired' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('SAR')
                    ->sortable(),
                
                TextColumn::make('currency')
                    ->label('Currency')
                    ->sortable(),
                
                TextColumn::make('buyer_phone')
                    ->label('Buyer Phone')
                    ->toggleable()
                    ->searchable(),
                
                TextColumn::make('order_reference_id')
                    ->label('Order Reference')
                    ->toggleable()
                    ->searchable(),
                
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
                
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'created' => 'Created',
                        'link_sent' => 'Link Sent',
                        'authorized' => 'Authorized',
                        'closed' => 'Closed',
                        'captured' => 'Captured',
                        'rejected' => 'Rejected',
                        'expired' => 'Expired',
                    ]),
                
                SelectFilter::make('provider')
                    ->options([
                        'tabby' => 'Tabby',
                    ]),
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
