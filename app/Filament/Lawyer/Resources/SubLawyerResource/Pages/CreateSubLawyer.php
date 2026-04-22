<?php

namespace App\Filament\Lawyer\Resources\SubLawyerResource\Pages;

use App\Filament\Lawyer\Resources\SubLawyerResource;
use App\Support\LawyerUserAccess;
use App\Traits\GeneralTrait;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateSubLawyer extends CreateRecord
{
    use GeneralTrait;

    protected static string $resource = SubLawyerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'admin';
        $data['parent_id'] = auth()->id();
        $data['status'] = 'active';
        $data['appointmentBookingType'] = 'both';
        $data['remember_token'] = Str::random(10);
        
        if (isset($data['phone'])) {
            $number = $this->convert2english($data['phone']);
            $data['phone'] = $this->phoneValidate($number);
        }
        
        return $data;
    }

    protected function afterCreate(): void
    {
        $user = $this->record;
        
        // Link the sub-lawyer to the workspace
        LawyerUserAccess::attach(auth()->id(), $user->id, 'sub_lawyer');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
