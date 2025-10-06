<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use App\Models\Payment;
use App\Models\ExpenseCheck;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;

    protected array $checkData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Extract data for Payment
        $paymentData = [
            'amount' => $data['amount'],
            'currency_id' => $data['currency_id'],
            'tax' => $data['tax'] ?? 0,
        ];

        // Create Payment
        $payment = Payment::create($paymentData);

        // Add payment_id to expense data
        $data['payment_id'] = $payment->id;

        // Extract check data
        $checkData = [
            'check_number' => $data['check_number'] ?? null,
            'bank_name' => $data['bank_name'] ?? null,
            'clearance_date' => $data['clearance_date'] ?? null,
            'deposit_account' => $data['deposit_account'] ?? null,
            'status_id' => $data['check_status_id'] ?? null,
        ];

        // Store check data temporarily
        $this->checkData = $checkData;

        // Remove check fields from expense data
        unset($data['check_number'], $data['bank_name'], $data['clearance_date'], $data['deposit_account'], $data['check_status_id'], $data['tax'], $data['total_after_tax']);

        return $data;
    }

    protected function afterCreate(): void
    {
        // Create ExpenseCheck if check data exists
        if (!empty(array_filter($this->checkData))) {
            $this->checkData['expense_id'] = $this->record->id;
            ExpenseCheck::create($this->checkData);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
