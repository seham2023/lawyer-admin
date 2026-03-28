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

        // Note: amount, currency_id, tax, etc. are NOT in Expense fillable,
        // they will be used in afterCreate to create the Payment record.

        return $data;
    }

    protected function afterCreate(): void
    {
        $data = $this->form->getState();

        // Create Payment record for this expense
        // This links the payment to the expense via morph relationship (payable)
        Payment::create([
            'amount' => $data['amount'] + ($data['amount'] * ($data['tax'] ?? 0) / 100),
            'tax' => $data['tax'] ?? 0,
            'currency_id' => $data['currency_id'],
            'pay_method_id' => $data['pay_method_id'],
            'user_id' => auth()->id(),
            'payable_type' => \App\Models\Expense::class,
            'payable_id' => $this->record->id,
            'status_id' => 2, // Typically marked as 'Paid' for an expense
        ]);

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
