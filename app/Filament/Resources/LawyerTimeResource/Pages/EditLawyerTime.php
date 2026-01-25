<?php

namespace App\Filament\Resources\LawyerTimeResource\Pages;

use App\Filament\Resources\LawyerTimeResource;
use App\Models\Qestass\Time;
use App\Models\Qestass\Interval;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Carbon\Carbon;

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

        // Load all times for this user with intervals
        $times = Time::where('user_id', $userId)->with('intervals')->get();

        // Initialize days arrays
        $onlineDays = $this->getDefaultDays();
        $offlineDays = $this->getDefaultDays();

        // Populate with existing data
        foreach ($times as $time) {
            $dayIndex = $this->getDayIndex($time->day);

            if ($dayIndex !== null) {
                // Group intervals into shifts (consecutive intervals form a shift)
                $shifts = $this->groupIntervalsIntoShifts($time->intervals);

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

    /**
     * Group consecutive intervals into shift time ranges
     */
    protected function groupIntervalsIntoShifts($intervals): array
    {
        if ($intervals->isEmpty()) {
            return [];
        }

        $shifts = [];
        $sortedIntervals = $intervals->sortBy('from')->values();

        $currentShiftStart = $sortedIntervals[0]->from;
        $currentShiftEnd = $sortedIntervals[0]->to;

        for ($i = 1; $i < $sortedIntervals->count(); $i++) {
            $interval = $sortedIntervals[$i];

            // If this interval starts where the previous one ended, extend the shift
            if ($interval->from === $currentShiftEnd) {
                $currentShiftEnd = $interval->to;
            } else {
                // Gap detected, save current shift and start a new one
                $shifts[] = [
                    'start_time' => $currentShiftStart,
                    'end_time' => $currentShiftEnd,
                ];
                $currentShiftStart = $interval->from;
                $currentShiftEnd = $interval->to;
            }
        }

        // Add the last shift
        $shifts[] = [
            'start_time' => $currentShiftStart,
            'end_time' => $currentShiftEnd,
        ];

        return $shifts;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // This won't be used since we're overriding handleRecordUpdate
        return $data;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $userId = auth()->id();

        // Delete existing times (and cascading intervals) for this user
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

                    // Generate intervals directly from shift time ranges
                    if (isset($dayData['shifts']) && is_array($dayData['shifts'])) {
                        foreach ($dayData['shifts'] as $shiftData) {
                            if (isset($shiftData['start_time']) && isset($shiftData['end_time'])) {
                                $this->generateIntervals(
                                    $time->id,
                                    $userId,
                                    $shiftData['start_time'],
                                    $shiftData['end_time']
                                );
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

                    // Generate intervals directly from shift time ranges
                    if (isset($dayData['shifts']) && is_array($dayData['shifts'])) {
                        foreach ($dayData['shifts'] as $shiftData) {
                            if (isset($shiftData['start_time']) && isset($shiftData['end_time'])) {
                                $this->generateIntervals(
                                    $time->id,
                                    $userId,
                                    $shiftData['start_time'],
                                    $shiftData['end_time']
                                );
                            }
                        }
                    }
                }
            }
        }

        return $record;
    }

    /**
     * Generate 30-minute intervals for a given time range
     */
    protected function generateIntervals(int $timeId, int $userId, string $startTime, string $endTime): void
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);
        $intervalDuration = 30; // minutes

        $current = $start->copy();

        while ($current->lt($end)) {
            $next = $current->copy()->addMinutes($intervalDuration);

            // Don't exceed end time
            if ($next->gt($end)) {
                break;
            }

            Interval::create([
                'from' => $current->format('H:i'),
                'to' => $next->format('H:i'),
                'time_id' => $timeId,
                'user_id' => $userId,
            ]);

            $current = $next->copy();
        }
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
