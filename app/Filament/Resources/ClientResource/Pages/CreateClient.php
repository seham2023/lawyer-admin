<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\Address;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['country_id']) || isset($data['state_id']) || isset($data['city_id'])) {
            $address = Address::create([
                'country_id' => $data['country_id'] ?? null,
                'state_id' => $data['state_id'] ?? null,
                'city_id' => $data['city_id'] ?? null,
                'address' => '', // or some default
            ]);
            $data['address_id'] = $address->id;
        }

        // Remove the address fields from data as they are not in clients table
        unset($data['country_id'], $data['state_id'], $data['city_id']);
        $data['appointmentBookingType'] = 'client';
        $data['type'] = 'user';
        $data['parent_id'] = auth()->id();
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
