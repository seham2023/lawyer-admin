<?php

namespace App\Filament\Resources\CaseResource\RelationManagers;

use Closure;
use Filament\Forms;
use Filament\Tables;
use App\Models\Payment;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\PaymentDetail;
use Filament\Forms\Components\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\RelationManagers\RelationManager;

class PaymentDetailRelationManager extends RelationManager
{
    protected static string $relationship = 'paymentDetails';
public static function getTitle(Model $ownerRecord, string $pageClass): string
{
    return __('payments');
}
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('payment_type')
                    ->label(__('payment_type'))
                    ->options([
                        'cash' => __('cash'),
                        'credit' => __('credit'),
                        'bank_transfer' => __('bank_transfer'),
                    ])
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->label(__('amount'))
                    ->required()
                    ->numeric()
                    ->rules([
                        function ($get) {
                            return function (string $attribute, $value, Closure $fail) use ($get) {
                                $payment = Payment::find($this->getOwnerRecord()?->payment_id);
                                if ($payment && $value > 0 && $payment->getRemainingPaymentAttribute() < $value) {
                                    $fail("Amount cannot exceed the remaining payment of {$payment->getRemainingPaymentAttribute()}.");
                                }
                            };
                        }
                    ]),
                Forms\Components\DateTimePicker::make('datetime')
                    ->label(__('datetime'))
                    ->required(),
                Forms\Components\Textarea::make('details')
                    ->label(__('details'))
                    ->nullable(),
                Hidden::make('payment_id')->default($this->getOwnerRecord()?->payment_id),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('name')),
                Tables\Columns\TextColumn::make('payment_type')
                    ->label(__('payment_type')),
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('amount')),
                Tables\Columns\TextColumn::make('datetime')
                    ->label(__('datetime')),
                Tables\Columns\TextColumn::make('details')
                    ->label(__('details')),
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
