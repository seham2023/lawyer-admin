<?php

namespace App\Filament\Resources\LawyerTimeResource\Pages;

use App\Filament\Resources\LawyerTimeResource;
use App\Models\Qestass\Time;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLawyerTimes extends ListRecords
{
    protected static string $resource = LawyerTimeResource::class;

    protected function getHeaderActions(): array
    {
        // Check if user has any times, if not show create, otherwise show edit
        $userId = auth()->id();
        $hasRecords = Time::where('user_id', $userId)->exists();

        if ($hasRecords) {
            // Get the first record to use for edit URL
            $firstRecord = Time::where('user_id', $userId)->first();

            return [
                Actions\Action::make('edit_schedule')
                    ->label(__('Edit Schedule'))
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->url(fn() => static::getResource()::getUrl('edit', ['record' => $firstRecord->id])),
            ];
        }

        return [
            Actions\Action::make('create_schedule')
                ->label(__('Create Schedule'))
                ->icon('heroicon-o-plus')
                ->color('success')
                ->url(fn() => static::getResource()::getUrl('create')),
        ];
    }
}
