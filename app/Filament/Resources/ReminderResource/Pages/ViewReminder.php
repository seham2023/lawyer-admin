<?php

namespace App\Filament\Resources\ReminderResource\Pages;

use App\Filament\Resources\ReminderResource;
use Filament\Resources\Pages\ViewRecord;

class ViewReminder extends ViewRecord
{
    protected static string $resource = ReminderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\EditAction::make(),
        ];
    }
}
