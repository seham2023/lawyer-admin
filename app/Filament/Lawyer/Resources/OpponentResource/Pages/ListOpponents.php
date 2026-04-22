<?php

namespace App\Filament\Lawyer\Resources\OpponentResource\Pages;

use App\Filament\Lawyer\Resources\OpponentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOpponents extends ListRecords
{
    protected static string $resource = OpponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
