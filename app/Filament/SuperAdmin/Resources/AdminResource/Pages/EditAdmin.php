<?php

namespace App\Filament\SuperAdmin\Resources\AdminResource\Pages;

use App\Filament\SuperAdmin\Resources\AdminResource;
use App\Traits\GeneralTrait;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdmin extends EditRecord
{
    use GeneralTrait;

    protected static string $resource = AdminResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure phone is properly formatted when updated
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
