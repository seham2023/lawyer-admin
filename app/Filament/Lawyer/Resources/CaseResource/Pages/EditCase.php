<?php

namespace App\Filament\Lawyer\Resources\CaseResource\Pages;

use App\Filament\Lawyer\Resources\CaseResource;
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

        // Load opponent data
        if ($this->record->opponent) {
            $data['opponent_name'] = $this->record->opponent->name;
            $data['opponent_email'] = $this->record->opponent->email;
            $data['opponent_country_key'] = $this->record->opponent->country_key;
            $data['opponent_mobile'] = $this->record->opponent->mobile;
            $data['opponent_location'] = $this->record->opponent->location;
            $data['opponent_nationality_id'] = $this->record->opponent->nationality_id;
        }

        // Load opponent lawyer data
        if ($this->record->opponent_lawyer) {
            $data['opponent_lawyer_name'] = $this->record->opponent_lawyer->name;
            $data['opponent_lawyer_country_key'] = $this->record->opponent_lawyer->country_key;
            $data['opponent_lawyer_mobile'] = $this->record->opponent_lawyer->mobile;
            $data['opponent_lawyer_email'] = $this->record->opponent_lawyer->email;
        }

        // Load financial data from the linked payment
        $payment = $this->record->payment;
        if ($payment) {
            $data['amount'] = $payment->amount;
            $data['currency_id'] = $payment->currency_id;
            $data['tax'] = $payment->tax;
            $data['pay_method_id'] = $payment->pay_method_id;
            $data['payment_status_id'] = $payment->status_id;
            $data['total_after_tax'] = $payment->amount + ($payment->amount * ($payment->tax ?? 0) / 100);
        }

        $this->form->fill($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // 1. Update or create opponent
        if (!empty($data['opponent_name'])) {
            $opponentData = [
                'name' => $data['opponent_name'],
                'email' => $data['opponent_email'] ?? null,
                'country_key' => $data['opponent_country_key'] ?? null,
                'mobile' => $data['opponent_mobile'] ?? null,
                'location' => $data['opponent_location'] ?? null,
                'nationality_id' => $data['opponent_nationality_id'] ?? null,
            ];

            if ($this->record->opponent) {
                $this->record->opponent->update($opponentData);
            } else {
                $opponent = Opponent::create($opponentData);
                $this->record->opponent_id = $opponent->id;
            }
        }

        // 2. Update or create opponent lawyer
        if (!empty($data['opponent_lawyer_name'])) {
            $lawyerData = [
                'name' => $data['opponent_lawyer_name'],
                'country_key' => $data['opponent_lawyer_country_key'] ?? null,
                'mobile' => $data['opponent_lawyer_mobile'] ?? null,
                'email' => $data['opponent_lawyer_email'] ?? null,
            ];

            if ($this->record->opponent_lawyer) {
                $this->record->opponent_lawyer->update($lawyerData);
            } else {
                $opponentLawyer = OpponentLawyer::create($lawyerData);
                $this->record->opponent_lawyer_id = $opponentLawyer->id;
            }
        }

        // 3. Update or create Payment
        $paymentData = [
            'amount' => $data['amount'] ?? 0,
            'currency_id' => $data['currency_id'] ?? null,
            'tax' => $data['tax'] ?? 0,
            'pay_method_id' => $data['pay_method_id'] ?? 1,
            'status_id' => $data['payment_status_id'] ?? 1,
            'user_id' => auth()->user()->parent_id ?? auth()->id(),
            'client_id' => $data['client_id'],
        ];

        if ($this->record->payment) {
            $this->record->payment->update($paymentData);
        } else {
            $this->record->payment()->create($paymentData);
        }

        // 4. Remove phantom fields not in case_records table
        unset(
            $data['opponent_name'],
            $data['opponent_email'],
            $data['opponent_country_key'],
            $data['opponent_mobile'],
            $data['opponent_location'],
            $data['opponent_nationality_id'],
            $data['opponent_lawyer_name'],
            $data['opponent_lawyer_country_key'],
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
