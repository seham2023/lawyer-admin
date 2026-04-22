<?php

namespace App\Filament\Lawyer\Resources\VisitResource\Pages;

use App\Filament\Lawyer\Resources\VisitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVisit extends EditRecord
{
    protected static string $resource = VisitResource::class;

    protected function afterSave(): void
    {
        $amount = $this->record->services()->sum('price');

        // Update payment record if it exists
        if ($this->record->payment) {
            $this->record->payment->update([
                'amount' => $amount,
            ]);
        } else if ($amount > 0) {
            // Create payment if it didn't exist but amount is now provided
            $this->record->payment()->create([
                'amount' => $amount,
                'tax' => 0,
                'currency_id' => \App\Support\Money::getCurrencyId(),
                'user_id' => auth()->id(),
                'client_id' => $this->record->client_id,
                'pay_method_id' => 1,
                'status_id' => 1,
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
