<?php

namespace App\Filament\Resources\OpponentLawyerResource\Pages;

use App\Filament\Resources\OpponentLawyerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOpponentLawyers extends ListRecords
{
    protected static string $resource = OpponentLawyerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
