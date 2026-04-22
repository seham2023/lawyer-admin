<?php

namespace App\Filament\Lawyer\Resources\SubLawyerResource\Pages;

use App\Filament\Lawyer\Resources\SubLawyerResource;
use App\Traits\GeneralTrait;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubLawyer extends EditRecord
{
    use GeneralTrait;

    protected static string $resource = SubLawyerResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['phone'])) {
            $number = $this->convert2english($data['phone']);
            $data['phone'] = $this->phoneValidate($number);
        }
        
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
