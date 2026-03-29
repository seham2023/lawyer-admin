<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    protected function fillForm(): void
    {
        $data = $this->record->toArray();

        // if ($this->record->address) {
        //     $data['country_id'] = $this->record->address->country_id;
        //     $data['state_id'] = $this->record->address->state_id;
        //     $data['city_id'] = $this->record->address->city_id;
        // }

        $this->form->fill($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // if ($this->record->address) {
        //     $this->record->address->update([
        //         'country_id' => $data['country_id'] ?? null,
        //         'state_id' => $data['state_id'] ?? null,
        //         'city_id' => $data['city_id'] ?? null,
        //     ]);
        // } else {
        //     $address = Address::create([
        //         'country_id' => $data['country_id'] ?? null,
        //         'state_id' => $data['state_id'] ?? null,
        //         'city_id' => $data['city_id'] ?? null,
        //         'address' => '',
        //     ]);
        //     $data['address_id'] = $address->id;
        // }

        // unset($data['country_id'], $data['state_id'], $data['city_id']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('detach_client')
                ->label(__('Detach Client'))
                ->icon('heroicon-o-link-slash')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading(__('Detach Client'))
                ->modalDescription(__('This will remove the client from your workspace without deleting the client record.'))
                ->action(function (): void {
                    \App\Models\LawyerClient::query()
                        ->where('lawyer_id', auth()->id())
                        ->where('client_id', $this->record->id)
                        ->delete();

                    \Filament\Notifications\Notification::make()
                        ->title(__('Client detached from your workspace.'))
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
