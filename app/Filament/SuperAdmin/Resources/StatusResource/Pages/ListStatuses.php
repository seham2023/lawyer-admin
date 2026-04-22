<?php

namespace App\Filament\SuperAdmin\Resources\StatusResource\Pages;

use App\Filament\SuperAdmin\Resources\StatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStatuses extends ListRecords
{
    use ListRecords\Concerns\Translatable;

    protected static string $resource = StatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\LocaleSwitcher::make(),
        ];
    }
}
