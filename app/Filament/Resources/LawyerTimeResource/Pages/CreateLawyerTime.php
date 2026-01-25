<?php

namespace App\Filament\Resources\LawyerTimeResource\Pages;

use App\Filament\Resources\LawyerTimeResource;
use App\Models\Qestass\Time;
use App\Models\Qestass\Interval;
use Filament\Resources\Pages\CreateRecord;
use Carbon\Carbon;

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

        // Return the first time record (or create a dummy one if none exist)
        return Time::where('user_id', $userId)->first() ?? new Time();
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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
