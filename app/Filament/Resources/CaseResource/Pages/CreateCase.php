<?php

namespace App\Filament\Resources\CaseResource\Pages;

use App\Filament\Resources\CaseResource;
use App\Models\Opponent;
use App\Models\OpponentLawyer;
use App\Models\Payment;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCase extends CreateRecord
{
    protected static string $resource = CaseResource::class;

    protected array $paymentData = [];
    protected ?int $courtId = null;

    public function mount(): void
    {
        parent::mount();

        if (request()->has('client_id')) {
            $this->data['client_id'] = request('client_id');
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Store payment data for after creation (polymorphic relationship)
        $this->paymentData = [
            'amount' => $data['amount'] ?? 0,
            'currency_id' => $data['currency_id'] ?? null,
            'tax' => $data['tax'] ?? 0,
            'pay_method_id' => $data['pay_method_id'] ?? null,
            'payment_status_id' => $data['payment_status_id'] ?? 1,
        ];

        // Store court_id for court history creation
        $this->courtId = $data['court_id'] ?? null;

        // Remove the fields that are not in case_records table
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
        // Create polymorphic payment for the case
        if (!empty($this->paymentData) && ($this->paymentData['amount'] > 0 || $this->paymentData['tax'] > 0)) {
            $this->record->payment()->create([
                'amount' => $this->paymentData['amount'],
                'currency_id' => $this->paymentData['currency_id'],
                'tax' => $this->paymentData['tax'],
                'user_id' => auth()->id(),
                'pay_method_id' => $this->paymentData['pay_method_id'] ?? 1, // Fallback to Cash ID 1 if not provided
                'status_id' => $this->paymentData['payment_status_id'] ?? 1, // Default to Pending
            ]);
        }

        // Create court history entry if court is assigned
        if (!empty($this->courtId)) {
            $this->record->courtHistory()->create([
                'court_id' => $this->courtId,
                'transfer_date' => now(),
                'transfer_reason' => 'Initial Filing',
                'is_current' => true,
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        if (request()->has('client_id')) {
            return \App\Filament\Resources\ClientResource::getUrl('view', ['record' => request('client_id')]);
        }

        return $this->getResource()::getUrl('index');
    }
}
