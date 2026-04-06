<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\Address;
use App\Models\Qestass\User;
use App\Support\LawyerUserAccess;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    protected bool $attachedExistingClient = false;
    protected bool $clientAlreadyLinked = false;

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

    protected function handleRecordCreation(array $data): Model
    {
        $lawyerId = auth()->id();
        $existingClient = $this->findExistingClient($data);

        if ($existingClient) {
            $wasAttached = LawyerUserAccess::attach($lawyerId, $existingClient->id);
            $this->attachedExistingClient = $wasAttached;
            $this->clientAlreadyLinked = ! $wasAttached;

            return $existingClient;
        }

        $record = static::getModel()::create($data);
        LawyerUserAccess::attach($lawyerId, $record->id);

        return $record;
    }

    protected function findExistingClient(array $data): ?User
    {
        $phoneMatch = null;
        $identityMatch = null;

        if (! empty($data['phone'])) {
            $phoneMatch = User::query()
                ->where('type', 'user')
                ->where('phone', $data['phone'])
                ->first();
        }

        if (! empty($data['identity_number'])) {
            $identityMatch = User::query()
                ->where('type', 'user')
                ->where('identity_number', $data['identity_number'])
                ->first();
        }

        if ($phoneMatch && $identityMatch && $phoneMatch->id !== $identityMatch->id) {
            throw ValidationException::withMessages([
                'phone' => __('This phone belongs to a different client record.'),
                'identity_number' => __('This identity number belongs to a different client record.'),
            ]);
        }

        return $phoneMatch ?? $identityMatch;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        if ($this->clientAlreadyLinked) {
            return __('Client already exists for your account.');
        }

        if ($this->attachedExistingClient) {
            return __('Existing client linked to your account.');
        }

        return __('Client created');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
