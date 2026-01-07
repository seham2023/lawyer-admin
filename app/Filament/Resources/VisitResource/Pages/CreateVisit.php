<?php

namespace App\Filament\Resources\VisitResource\Pages;

use App\Filament\Resources\VisitResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateVisit extends CreateRecord
{
    protected static string $resource = VisitResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }

    protected function afterCreate(): void
    {
        // Create payment if amount is provided
        $data = $this->form->getState();

        if (isset($data['amount']) && $data['amount'] > 0) {
            $totalAmount = $data['amount'] + ($data['amount'] * ($data['tax'] ?? 0) / 100);

            \App\Models\Payment::create([
                'amount' => $totalAmount,
                'tax' => $data['tax'] ?? 0,
                'currency_id' => $data['currency_id'],
                'user_id' => auth()->id(),
                'client_id' => $this->record->client_id,
                'pay_method_id' => $data['pay_method_id'] ?? null,
                'status_id' => $data['payment_status_id'] ?? 1,
                'payable_type' => \App\Models\Visit::class,
                'payable_id' => $this->record->id,
            ]);
        }
    }
}
