<?php

namespace App\Filament\Lawyer\Resources\ExpenseResource\Pages;

use App\Filament\Lawyer\Resources\ExpenseResource;
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

        // Store payment data
        $this->paymentData = [
            'amount' => $data['amount'] ?? 0,
            'currency_id' => $data['currency_id'] ?? null,
            'tax' => $data['tax'] ?? 0,
            'pay_method_id' => $data['pay_method_id'] ?? null,
            'payment_status_id' => $data['payment_status_id'] ?? 1,
        ];

        // Unset payment fields so they don't get saved to the expense record
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

    protected array $paymentData = [];

    protected function afterCreate(): void
    {
        // Create Payment record for this expense
        // This links the payment to the expense via morph relationship (payable)
        if (!empty($this->paymentData)) {
            $amount = $this->paymentData['amount'];
            $tax = $this->paymentData['tax'];
            
            Payment::create([
                'amount' => $amount + ($amount * $tax / 100),
                'tax' => $tax,
                'currency_id' => $this->paymentData['currency_id'],
                'pay_method_id' => $this->paymentData['pay_method_id'],
                'user_id' => auth()->id(),
                'payable_type' => \App\Models\Expense::class,
                'payable_id' => $this->record->id,
                'status_id' => $this->paymentData['payment_status_id'],
            ]);
        }

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
