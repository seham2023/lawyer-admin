<?php

namespace App\Filament\SuperAdmin\Resources\StatusResource\Pages;

use App\Filament\SuperAdmin\Resources\StatusResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStatus extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;

    protected static string $resource = StatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }
}
