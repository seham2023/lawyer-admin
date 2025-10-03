<?php

namespace App\Filament\Resources\CaseResource\Pages;

use App\Filament\Resources\CaseResource;
use App\Models\Opponent;
use App\Models\OpponentLawyer;
use App\Models\Payment;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCase extends EditRecord
{
    protected static string $resource = CaseResource::class;

    protected function fillForm(): void
    {
        $data = $this->record->toArray();

        if ($this->record->opponent) {
            $data['opponent_name'] = $this->record->opponent->name;
            $data['opponent_email'] = $this->record->opponent->email;
            $data['opponent_mobile'] = $this->record->opponent->mobile;
            $data['opponent_location'] = $this->record->opponent->location;
            $data['opponent_nationality_id'] = $this->record->opponent->nationality_id;
        }

        if ($this->record->opponent_lawyer) {
            $data['opponent_lawyer_name'] = $this->record->opponent_lawyer->name;
            $data['opponent_lawyer_mobile'] = $this->record->opponent_lawyer->mobile;
            $data['opponent_lawyer_email'] = $this->record->opponent_lawyer->email;
        }

        if ($this->record->payment) {
            $data['amount'] = $this->record->payment->amount;
            $data['currency_id'] = $this->record->payment->currency_id;
            $data['tax'] = $this->record->payment->tax;
        }

        $this->form->fill($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Update opponent
        if ($this->record->opponent) {
            $this->record->opponent->update([
                'name' => $data['opponent_name'] ?? null,
                'email' => $data['opponent_email'] ?? null,
                'mobile' => $data['opponent_mobile'] ?? null,
                'location' => $data['opponent_location'] ?? null,
                'nationality_id' => $data['opponent_nationality_id'] ?? null,
            ]);
        }

        // Update opponent lawyer
        if ($this->record->opponent_lawyer) {
            $this->record->opponent_lawyer->update([
                'name' => $data['opponent_lawyer_name'] ?? null,
                'mobile' => $data['opponent_lawyer_mobile'] ?? null,
                'email' => $data['opponent_lawyer_email'] ?? null,
            ]);
        }

        // Update payment
        if ($this->record->payment) {
            $this->record->payment->update([
                'amount' => $data['amount'] ?? 0,
                'currency_id' => $data['currency_id'] ?? null,
                'tax' => $data['tax'] ?? 0,
            ]);
        }

        // Remove the fields that are not in case_records table
        unset(
            $data['opponent_name'],
            $data['opponent_email'],
            $data['opponent_mobile'],
            $data['opponent_location'],
            $data['opponent_nationality_id'],
            $data['opponent_lawyer_name'],
            $data['opponent_lawyer_mobile'],
            $data['opponent_lawyer_email'],
            $data['amount'],
            $data['currency_id'],
            $data['tax'],
            $data['total_after_tax']
        );

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
