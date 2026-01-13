<?php

namespace App\Filament\Resources\CaseResource\RelationManagers;

use App\Models\PaymentSession;
use App\Services\TabbyPaymentService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Notifications\Notification;

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
                    ->color(fn(string $state): string => match ($state) {
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
                Tables\Actions\Action::make('createTabbySession')
                    ->label('Create Tabby Session')
                    ->icon('heroicon-o-credit-card')
                    ->color('success')
                    ->form(function () {
                        $caseRecord = $this->getOwnerRecord();

                        // Load the client relationship if not already loaded
                        if (!$caseRecord->relationLoaded('client')) {
                            $caseRecord->load('client');
                        }

                        $client = $caseRecord->client;

                        return [
                            Forms\Components\TextInput::make('buyer_name')
                                ->label('Buyer Name')
                                ->required()
                                ->default($client?->name ?? 'Customer')
                                ->helperText('Enter the buyer\'s full name'),

                            Forms\Components\TextInput::make('buyer_email')
                                ->label('Buyer Email')
                                ->email()
                                ->required()
                                ->default($client?->email ?? 'customer@example.com')
                                ->helperText('Enter the buyer\'s email address'),

                            Forms\Components\TextInput::make('buyer_phone')
                                ->label('Buyer Phone')
                                ->tel()
                                ->required()
                                ->default($client?->phone ?? '')
                                ->placeholder('5XXXXXXXX')
                                ->helperText('Enter phone number without country code (e.g., 566950500)'),
                        ];
                    })
                    ->action(function (array $data) {
                        $caseRecord = $this->getOwnerRecord();

                        // Get the payment associated with this case
                        $payment = $caseRecord->payment;

                        if (!$payment) {
                            Notification::make()
                                ->title('No Payment Found')
                                ->body('This case does not have an associated payment.')
                                ->danger()
                                ->send();
                            return;
                        }

                        if (!$payment->amount || $payment->amount <= 0) {
                            Notification::make()
                                ->title('Invalid Payment Amount')
                                ->body('The payment amount must be greater than zero.')
                                ->danger()
                                ->send();
                            return;
                        }

                        try {
                            // Create Tabby payment session
                            $tabbyService = new TabbyPaymentService();

                            $result = $tabbyService->createSession(
                                amount: $payment->amount,
                                currency: 'SAR',
                                buyerPhone: $data['buyer_phone'],
                                orderReferenceId: 'CASE-' . $caseRecord->id . '-' . time(),
                                items: [
                                    [
                                        'title' => 'Legal Services - Case: ' . $caseRecord->subject,
                                        'quantity' => 1,
                                        'unit_price' => number_format($payment->amount, 2, '.', ''),
                                        'category' => 'Legal Services',
                                    ]
                                ],
                                merchantCode: null,
                                buyerName: $data['buyer_name'],
                                buyerEmail: $data['buyer_email']
                            );

                            if ($result['success']) {
                                // Update the payment session with case_record_id
                                if (isset($result['payment_session'])) {
                                    $result['payment_session']->update([
                                        'case_record_id' => $caseRecord->id,
                                    ]);
                                }

                                Notification::make()
                                    ->title('Tabby Session Created')
                                    ->body('Payment session created successfully. Session ID: ' . $result['session_id'])
                                    ->success()
                                    ->send();

                                // Optionally send the payment link
                                if (isset($result['session_id'])) {
                                    $tabbyService->sendPaymentLink($result['session_id']);
                                }
                            } else {
                                Notification::make()
                                    ->title('Failed to Create Session')
                                    ->body($result['error'] ?? 'An unknown error occurred.')
                                    ->danger()
                                    ->send();
                            }
                        } catch (\RuntimeException $e) {
                            // Handle configuration errors
                            Notification::make()
                                ->title('Configuration Error')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        } catch (\Exception $e) {
                            // Handle any other errors
                            Notification::make()
                                ->title('Error')
                                ->body('An unexpected error occurred: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
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
