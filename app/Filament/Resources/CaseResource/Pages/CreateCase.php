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
        // Create opponent only if name is not null
        if (!empty($data['opponent_name'])) {
            $opponent = Opponent::create([
                'name' => $data['opponent_name'],
                'email' => $data['opponent_email'] ?? null,
                'mobile' => $data['opponent_mobile'] ?? null,
                'location' => $data['opponent_location'] ?? null,
                'nationality_id' => $data['opponent_nationality_id'] ?? null,
            ]);
            $data['opponent_id'] = $opponent->id;
        } else {
            $data['opponent_id'] = null;
        }

        // Create opponent lawyer only if name is not null
        if (!empty($data['opponent_lawyer_name'])) {
            $opponentLawyer = OpponentLawyer::create([
                'name' => $data['opponent_lawyer_name'],
                'mobile' => $data['opponent_lawyer_mobile'] ?? null,
                'email' => $data['opponent_lawyer_email'] ?? null,
            ]);
            $data['opponent_lawyer_id'] = $opponentLawyer->id;
        } else {
            $data['opponent_lawyer_id'] = null;
        }

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
                'pay_method_id' => $this->paymentData['pay_method_id'] ?? null,
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
