<?php

namespace App\Filament\Resources\VisitResource\Pages;

use App\Filament\Resources\VisitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVisit extends EditRecord
{
    protected static string $resource = VisitResource::class;

    protected function fillForm(): void
    {
        $data = $this->record->toArray();

        // Populate payment data if it exists
        if ($this->record->payment) {
            $data['currency_id'] = $this->record->payment->currency_id;
            
            // For editing, we show the net amount (amount field in Payment)
            $data['amount'] = $this->record->payment->amount;
            $data['tax'] = $this->record->payment->tax;
            
            // Calculate total for display
            $data['total_after_tax'] = $data['amount'] + ($data['amount'] * ($data['tax'] ?? 0) / 100);
            
            $data['pay_method_id'] = $this->record->payment->pay_method_id;
            $data['payment_status_id'] = $this->record->payment->status_id;
        }

        $this->form->fill($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Update payment record if it exists
        if ($this->record->payment) {
            $this->record->payment->update([
                'amount' => $data['amount'] ?? 0,
                'tax' => $data['tax'] ?? 0,
                'currency_id' => $data['currency_id'],
                'pay_method_id' => $data['pay_method_id'] ?? 1,
                'status_id' => $data['payment_status_id'] ?? 1,
            ]);
        } else if (isset($data['amount']) && $data['amount'] > 0) {
            // Create payment if it didn't exist but amount is now provided
            $this->record->payment()->create([
                'amount' => $data['amount'],
                'tax' => $data['tax'] ?? 0,
                'currency_id' => $data['currency_id'],
                'user_id' => auth()->id(),
                'client_id' => $this->record->client_id,
                'pay_method_id' => $data['pay_method_id'] ?? 1,
                'status_id' => $data['payment_status_id'] ?? 1,
            ]);
        }

        // Cleanup fields that are not in visits table
        unset(
            $data['amount'],
            $data['currency_id'],
            $data['tax'],
            $data['pay_method_id'],
            $data['payment_status_id'],
            $data['total_after_tax']
        );

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
