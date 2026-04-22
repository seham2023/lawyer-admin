<?php

namespace App\Filament\Lawyer\Resources\NoteResource\Pages;

use App\Filament\Lawyer\Resources\NoteResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateNote extends CreateRecord
{
    protected static string $resource = NoteResource::class;
}
