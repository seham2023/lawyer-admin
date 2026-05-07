<?php

namespace App\Filament\Lawyer\Resources\CaseResource\RelationManagers;

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

    public static function getModelLabel(): string
    {
        return __('Payment Details');
    }

    public static function getPluralModelLabel(): string
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
                    ->minValue(0.01)
                    ->rules([
                        function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                $payment = $this->getOwnerRecord()->payment;
                                if (!$payment) return;
                                
                                $remaining = $payment->remaining_payment;
                                if ($value > $remaining) {
                                    $fail(__('payment.amount_exceeds_remaining', ['amount' => $remaining]));
                                }
                            };
                        }
                    ]),
                Forms\Components\DateTimePicker::make('paid_at')
                    ->label(__('paid_at'))
                    ->required(),
                Forms\Components\Textarea::make('details')
                    ->label(__('details'))
                    ->nullable(),
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
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Automatically set payment_id from the parent case's payment
                        $data['payment_id'] = $this->ownerRecord->payment?->id;
                        return $data;
                    })
                    ->before(function (Tables\Actions\CreateAction $action) {
                        // Check if case has a payment
                        if (!$this->ownerRecord->payment) {
                            \Filament\Notifications\Notification::make()
                                ->title(__('No payment found'))
                                ->body(__('payment.create_case_first'))
                                ->danger()
                                ->send();

                            $action->halt();
                        }
                    })
                    ->after(function () {
                        // Refresh the parent to update remaining balance
                        $this->ownerRecord->refresh();
                    }),
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
