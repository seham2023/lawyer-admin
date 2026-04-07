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
        $amount = $this->record->services()->sum('price');
        
        // Create polymorphic payment for the visit
        if ($amount > 0) {
            $this->record->payment()->create([
                'amount' => $amount,
                'tax' => 0,
                'currency_id' => \App\Support\Money::getCurrencyId(),
                'user_id' => auth()->id(),
                'client_id' => $this->record->client_id,
                'pay_method_id' => 1, // Fallback to Cash ID 1
                'status_id' => 1, // Default to Pending
            ]);
        }
    }
}
