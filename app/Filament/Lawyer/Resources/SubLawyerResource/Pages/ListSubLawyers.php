<?php

namespace App\Filament\Lawyer\Resources\SubLawyerResource\Pages;

use App\Filament\Lawyer\Resources\SubLawyerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubLawyers extends ListRecords
{
    protected static string $resource = SubLawyerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
