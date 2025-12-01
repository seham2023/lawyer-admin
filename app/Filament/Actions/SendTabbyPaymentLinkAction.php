<?php

namespace App\Filament\Actions;

use App\Models\CaseRecord;
use App\Models\User;
use App\Services\TabbyPaymentService;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class SendTabbyPaymentLinkAction
{
    public static function make(): Action
    {
        return Action::make('send_tabby_payment_link')
            ->label('Send Tabby Payment Link')
            ->icon('heroicon-o-link')
            ->color('success')
            ->requiresConfirmation()
            ->form([
                Select::make('user_id')
                    ->label('User')
                    ->options(function (Model $record) {
                        if ($record instanceof CaseRecord) {
                            return [$record->user_id => $record->user->name];
                        }
                        return [];
                    })
                    ->default(function (Model $record) {
                        if ($record instanceof CaseRecord) {
                            return $record->user_id;
                        }
                        return null;
                    })
                    ->required()
                    ->reactive(),

                TextInput::make('amount')
                    ->label('Amount')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->minValue(0.01)
                    ->step(0.01)
                    ->helperText('Enter the amount to charge the client'),

                TextInput::make('order_reference')
                    ->label('Order Reference')
                    ->required()
                    ->default(function (Model $record) {
                        if ($record instanceof CaseRecord) {
                            return 'CASE-' . $record->id;
                        }
                        return '';
                    })
                    ->helperText('Reference for this payment order'),
            ])
            ->action(function (array $data, Model $record) {
                if (!$record instanceof CaseRecord) {
                    Notification::make()
                        ->title('Error')
                        ->body('This action can only be used on Case records.')
                        ->danger()
                        ->send();

                    return;
                }

                $user = User::find($data['user_id']);
                if (!$user) {
                    Notification::make()
                        ->title('Error')
                        ->body('Selected user not found.')
                        ->danger()
                        ->send();

                    return;
                }

                // Validate that user has a phone number
                if (empty($user->phone)) {
                    Notification::make()
                        ->title('Error')
                        ->body('User does not have a phone number. Please update user information first.')
                        ->danger()
                        ->send();

                    return;
                }

                // Prepare items for Tabby
                $items = [
                    [
                        'title' => 'Legal Services for Case #' . $record->id,
                        'quantity' => 1,
                        'unit_price' => $data['amount'],
                        'category' => 'Legal Services',
                    ]
                ];

                // Initialize Tabby service
                $tabbyService = new TabbyPaymentService();

                // Create payment session
                $result = $tabbyService->createSession(
                    amount: (float) $data['amount'],
                    currency: 'SAR', // Saudi Arabia currency
                    buyerPhone: $user->phone,
                    orderReferenceId: $data['order_reference'],
                    items: $items
                );

                if (!$result['success']) {
                    Notification::make()
                        ->title('Payment Session Creation Failed')
                        ->body($result['error'] ?? 'Unknown error occurred')
                        ->danger()
                        ->send();

                    return;
                }

                // Send payment link to customer
                $linkSent = $tabbyService->sendPaymentLink($result['session_id']);

                if ($linkSent) {
                    Notification::make()
                        ->title('Payment Link Sent Successfully')
                        ->body('A payment link has been sent to the client via SMS.')
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Payment Link Sent Failed')
                        ->body('The payment session was created, but failed to send the link to the client.')
                        ->warning()
                        ->send();
                }
            });
    }
}
