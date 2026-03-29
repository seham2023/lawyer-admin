<?php

namespace App\Filament\Resources\VisitResource\Pages;

use App\Filament\Resources\VisitResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateVisit extends CreateRecord
{
    protected static string $resource = VisitResource::class;

    protected array $paymentData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        // Store payment data for after creation (polymorphic relationship)
        $this->paymentData = [
            'amount' => $data['amount'] ?? 0,
            'currency_id' => $data['currency_id'] ?? null,
            'tax' => $data['tax'] ?? 0,
            'pay_method_id' => $data['pay_method_id'] ?? null,
            'payment_status_id' => $data['payment_status_id'] ?? 1,
        ];

        // Remove the fields that are not in visits table
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

    protected function afterCreate(): void
    {
        // Create polymorphic payment for the visit
        if (!empty($this->paymentData) && ($this->paymentData['amount'] > 0 || $this->paymentData['tax'] > 0)) {
            $this->record->payment()->create([
                'amount' => $this->paymentData['amount'] + ($this->paymentData['amount'] * ($this->paymentData['tax'] ?? 0) / 100),
                'tax' => $this->paymentData['tax'] ?? 0,
                'currency_id' => $this->paymentData['currency_id'],
                'user_id' => auth()->id(),
                'client_id' => $this->record->client_id,
                'pay_method_id' => $this->paymentData['pay_method_id'] ?? 1, // Fallback to Cash ID 1 if not provided
                'status_id' => $this->paymentData['payment_status_id'] ?? 1, // Default to Pending
            ]);
        }
    }
}
