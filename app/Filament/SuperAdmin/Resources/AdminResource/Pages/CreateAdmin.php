<?php

namespace App\Filament\SuperAdmin\Resources\AdminResource\Pages;

use App\Filament\SuperAdmin\Resources\AdminResource;
use App\Traits\GeneralTrait;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateAdmin extends CreateRecord
{
    use GeneralTrait;

    protected static string $resource = AdminResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set necessary defaults and transformations
        $data['type'] = 'admin';
        $data['parent_id'] = auth()->id();
        $data['status'] = 'active';
        $data['appointmentBookingType'] = 'both';
        $data['remember_token'] = Str::random(10);
        
        // Ensure phone is properly formatted
        $number = $this->convert2english($data['phone']);
        $data['phone'] = $this->phoneValidate($number);
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
