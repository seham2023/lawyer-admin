<?php

namespace App\Filament\Resources\LawyerTimeResource\Pages;

use App\Filament\Resources\LawyerTimeResource;
use App\Models\Qestass\Time;
use App\Models\Qestass\Shift;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLawyerTime extends EditRecord
{
    protected static string $resource = LawyerTimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $userId = auth()->id();

        // Load all times for this user with shifts
        $times = Time::where('user_id', $userId)->with('shifts')->get();

        // Initialize days arrays
        $onlineDays = $this->getDefaultDays();
        $offlineDays = $this->getDefaultDays();

        // Populate with existing data
        foreach ($times as $time) {
            $dayIndex = $this->getDayIndex($time->day);

            if ($dayIndex !== null) {
                $shifts = $time->shifts->map(function ($shift) {
                    return [
                        'start_time' => $shift->start_time,
                        'end_time' => $shift->end_time,
                    ];
                })->toArray();

                if ($time->type === 'online') {
                    $onlineDays[$dayIndex] = [
                        'day' => $time->day,
                        'enabled' => true,
                        'shifts' => $shifts,
                    ];
                } else {
                    $offlineDays[$dayIndex] = [
                        'day' => $time->day,
                        'enabled' => true,
                        'shifts' => $shifts,
                    ];
                }
            }
        }

        return [
            'online_days' => array_values($onlineDays),
            'offline_days' => array_values($offlineDays),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // This won't be used since we're overriding handleRecordUpdate
        return $data;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
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

        return $record;
    }

    protected function getDefaultDays(): array
    {
        return [
            ['day' => 'saturday', 'enabled' => false, 'shifts' => []],
            ['day' => 'sunday', 'enabled' => false, 'shifts' => []],
            ['day' => 'monday', 'enabled' => false, 'shifts' => []],
            ['day' => 'tuesday', 'enabled' => false, 'shifts' => []],
            ['day' => 'wednesday', 'enabled' => false, 'shifts' => []],
            ['day' => 'thursday', 'enabled' => false, 'shifts' => []],
            ['day' => 'friday', 'enabled' => false, 'shifts' => []],
        ];
    }

    protected function getDayIndex(string $day): ?int
    {
        $days = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        $index = array_search($day, $days);
        return $index !== false ? $index : null;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
