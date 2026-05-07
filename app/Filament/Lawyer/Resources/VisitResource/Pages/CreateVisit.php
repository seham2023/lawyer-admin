<?php

namespace App\Filament\Lawyer\Resources\VisitResource\Pages;

use App\Filament\Lawyer\Resources\VisitResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateVisit extends CreateRecord
{
    protected static string $resource = VisitResource::class;

    public function mount(): void
    {
        parent::mount();

        if (request()->has('client_id')) {
            $this->data['client_id'] = request('client_id');
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        if (request()->has('client_id')) {
            return \App\Filament\Lawyer\Resources\ClientResource::getUrl('view', ['record' => request('client_id')]);
        }

        return $this->getResource()::getUrl('index');
    }
}
