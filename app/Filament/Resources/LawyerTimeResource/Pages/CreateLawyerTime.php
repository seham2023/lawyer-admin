<?php

namespace App\Filament\Resources\LawyerTimeResource\Pages;

use App\Filament\Resources\LawyerTimeResource;
use App\Models\Qestass\Time;
use App\Models\Qestass\Shift;
use Filament\Resources\Pages\CreateRecord;

class CreateLawyerTime extends CreateRecord
{
    protected static string $resource = LawyerTimeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // This won't be used since we're overriding handleRecordCreation
        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $userId = auth()->id();

        // Delete existing times (and cascading shifts/intervals) for this user
        Time::where('user_id', $userId)->delete();

        // Process online days
        if (isset($data['online_days'])) {
            foreach ($data['online_days'] as $dayData) {
                if ($dayData['enabled'] ?? false) {
                    $time = Time::create([
                        'day' => $dayData['day'],
                        'type' => 'online',
                        'user_id' => $userId,
                    ]);

                    // Create shifts for this day (intervals will be auto-generated)
                    if (isset($dayData['shifts']) && is_array($dayData['shifts'])) {
                        foreach ($dayData['shifts'] as $shiftData) {
                            if (isset($shiftData['start_time']) && isset($shiftData['end_time'])) {
                                Shift::create([
                                    'start_time' => $shiftData['start_time'],
                                    'end_time' => $shiftData['end_time'],
                                    'time_id' => $time->id,
                                    'user_id' => $userId,
                                ]);
                                // Intervals are auto-generated via Shift model boot method
                            }
                        }
                    }
                }
            }
        }

        // Process offline days
        if (isset($data['offline_days'])) {
            foreach ($data['offline_days'] as $dayData) {
                if ($dayData['enabled'] ?? false) {
                    $time = Time::create([
                        'day' => $dayData['day'],
                        'type' => 'offline',
                        'user_id' => $userId,
                    ]);

                    // Create shifts for this day (intervals will be auto-generated)
                    if (isset($dayData['shifts']) && is_array($dayData['shifts'])) {
                        foreach ($dayData['shifts'] as $shiftData) {
                            if (isset($shiftData['start_time']) && isset($shiftData['end_time'])) {
                                Shift::create([
                                    'start_time' => $shiftData['start_time'],
                                    'end_time' => $shiftData['end_time'],
                                    'time_id' => $time->id,
                                    'user_id' => $userId,
                                ]);
                                // Intervals are auto-generated via Shift model boot method
                            }
                        }
                    }
                }
            }
        }

        // Return the first time record (or create a dummy one if none exist)
        return Time::where('user_id', $userId)->first() ?? new Time();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
