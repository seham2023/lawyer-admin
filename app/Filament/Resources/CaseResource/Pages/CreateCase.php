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

        // Create payment
        $payment = Payment::create([
            'amount' => $data['amount'] ?? 0,
            'currency_id' => $data['currency_id'] ?? null,
            'tax' => $data['tax'] ?? 0,
        ]);
        $data['payment_id'] = $payment->id;

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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
