<?php

namespace App\Filament\SuperAdmin\Resources\StatusResource\Pages;

use App\Filament\SuperAdmin\Resources\StatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStatus extends EditRecord
{
    use EditRecord\Concerns\Translatable;

    protected static string $resource = StatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\LocaleSwitcher::make(),
        ];
    }
}
