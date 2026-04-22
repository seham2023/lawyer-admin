<?php

namespace App\Filament\Lawyer\Resources\EmailResource\Pages;

use App\Filament\Lawyer\Resources\EmailResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEmail extends CreateRecord
{
    protected static string $resource = EmailResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
