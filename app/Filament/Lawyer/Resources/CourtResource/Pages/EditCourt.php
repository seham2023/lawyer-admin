<?php

namespace App\Filament\Lawyer\Resources\CourtResource\Pages;

use App\Filament\Lawyer\Resources\CourtResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCourt extends EditRecord
{
    protected static string $resource = CourtResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
