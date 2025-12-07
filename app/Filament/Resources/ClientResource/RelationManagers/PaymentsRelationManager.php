<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Models\Payment;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $recordTitleAttribute = 'amount';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('case_record_id')
                    ->label(__('case'))
                    ->relationship('caseRecord', 'subject', function ($query) {
                        return $query->where('client_id', $this->getOwnerRecord()->id);
                    })
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Forms\Components\TextInput::make('amount')
                    ->label(__('amount'))
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('tax')
                    ->label(__('tax'))
                    ->numeric()
                    ->default(0)
                    ->required(),

                Forms\Components\Select::make('currency_id')
                    ->label(__('currency'))
                    ->relationship('currency', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\DatePicker::make('payment_date')
                    ->label(__('payment_date'))
                    ->native(false)
                    ->default(now()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount')
            ->columns([
                Tables\Columns\TextColumn::make('caseRecord.subject')
                    ->label(__('case'))
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('payment_date')
                    ->label(__('payment_date'))
                    ->date()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label(__('amount'))
                    ->money('default')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tax')
                    ->label(__('tax'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('currency.name')
                    ->label(__('currency'))
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_paid')
                    ->label(__('total_paid'))
                    ->money('default')
                    ->getStateUsing(function ($record) {
                        return $record->totalPaid;
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('remaining_payment')
                    ->label(__('remaining'))
                    ->money('default')
                    ->getStateUsing(function ($record) {
                        return $record->remainingPayment;
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
