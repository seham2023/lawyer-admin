<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use App\Models\ExpenseCheck;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExpense extends EditRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load check data
        $check = $this->record->check;
        if ($check) {
            $data['check_number'] = $check->check_number;
            $data['bank_name'] = $check->bank_name;
            $data['clearance_date'] = $check->clearance_date;
            $data['deposit_account'] = $check->deposit_account;
            $data['check_status_id'] = $check->status_id;
        }

        // Load tax from payment
        $payment = $this->record->payment;
        if ($payment) {
            $data['tax'] = $payment->tax;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Update Payment
        $payment = $this->record->payment;
        if ($payment) {
            $payment->update([
                'amount' => $data['amount'],
                'currency_id' => $data['currency_id'],
                'tax' => $data['tax'] ?? 0,
            ]);
        }

        // Extract check data
        $checkData = [
            'check_number' => $data['check_number'] ?? null,
            'bank_name' => $data['bank_name'] ?? null,
            'clearance_date' => $data['clearance_date'] ?? null,
            'deposit_account' => $data['deposit_account'] ?? null,
            'status_id' => $data['check_status_id'] ?? null,
        ];

        // Update or create ExpenseCheck
        $check = $this->record->check;
        if ($check) {
            $check->update($checkData);
        } elseif (!empty(array_filter($checkData))) {
            $checkData['expense_id'] = $this->record->id;
            ExpenseCheck::create($checkData);
        }

        // Remove fields not in Expense fillable
        unset($data['check_number'], $data['bank_name'], $data['clearance_date'], $data['deposit_account'], $data['check_status_id'], $data['tax'], $data['total_after_tax']);

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
