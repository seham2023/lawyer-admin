<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\Address;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    protected function fillForm(): void
    {
        $data = $this->record->toArray();

        if ($this->record->address) {
            $data['country_id'] = $this->record->address->country_id;
            $data['state_id'] = $this->record->address->state_id;
            $data['city_id'] = $this->record->address->city_id;
        }

        $this->form->fill($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->record->address) {
            $this->record->address->update([
                'country_id' => $data['country_id'] ?? null,
                'state_id' => $data['state_id'] ?? null,
                'city_id' => $data['city_id'] ?? null,
            ]);
        } else {
            $address = Address::create([
                'country_id' => $data['country_id'] ?? null,
                'state_id' => $data['state_id'] ?? null,
                'city_id' => $data['city_id'] ?? null,
                'address' => '',
            ]);
            $data['address_id'] = $address->id;
        }

        unset($data['country_id'], $data['state_id'], $data['city_id']);

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
