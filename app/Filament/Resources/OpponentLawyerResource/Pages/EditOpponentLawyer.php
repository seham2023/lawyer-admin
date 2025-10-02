<?php

namespace App\Filament\Resources\OpponentLawyerResource\Pages;

use App\Filament\Resources\OpponentLawyerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOpponentLawyer extends EditRecord
{
    protected static string $resource = OpponentLawyerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
