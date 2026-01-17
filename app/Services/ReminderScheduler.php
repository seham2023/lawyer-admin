<?php

namespace App\Services;

use Carbon\Carbon;
use InvalidArgumentException;

class ReminderScheduler
{
    /**
     * Calculate the reminder time based on event datetime and offset.
     */
    public function calculateReminderTime($eventDateTime, string $offset): Carbon
    {
        $eventTime = Carbon::parse($eventDateTime);
        $offsetData = $this->parseOffset($offset);

        return $eventTime->copy()->sub($offsetData['value'], $offsetData['unit']);
    }

    /**
     * Parse offset string (e.g., "1 day", "2 hours", "30 minutes") into value and unit.
     */
    public function parseOffset(string $offsetString): array
    {
        // Trim and normalize the string
        $offsetString = trim(strtolower($offsetString));

        // Match pattern: number + space + unit
        if (preg_match('/^(\d+)\s*(minute|minutes|hour|hours|day|days|week|weeks)$/', $offsetString, $matches)) {
            $value = (int) $matches[1];
            $unit = $matches[2];

            // Normalize unit to singular form for Carbon
            $unitMap = [
                'minute' => 'minute',
                'minutes' => 'minute',
                'hour' => 'hour',
                'hours' => 'hour',
                'day' => 'day',
                'days' => 'day',
                'week' => 'week',
                'weeks' => 'week',
            ];

            return [
                'value' => $value,
                'unit' => $unitMap[$unit],
            ];
        }

        throw new InvalidArgumentException("Invalid offset format: {$offsetString}. Expected format: '1 day', '2 hours', etc.");
    }

    /**
     * Get user's timezone from settings.
     */
    public function getUserTimezone(int $userId): string
    {
        $settingsService = app(UserSettingsService::class);
        return $settingsService->getUserTimezone($userId);
    }

    /**
     * Convert a datetime to user's timezone.
     */
    public function convertToUserTimezone($datetime, int $userId): Carbon
    {
        $timezone = $this->getUserTimezone($userId);
        return Carbon::parse($datetime)->timezone($timezone);
    }

    /**
     * Convert a datetime from user's timezone to UTC.
     */
    public function convertToUtc($datetime, int $userId): Carbon
    {
        $timezone = $this->getUserTimezone($userId);
        return Carbon::parse($datetime, $timezone)->utc();
    }

    /**
     * Check if a reminder should be sent now.
     */
    public function shouldSendNow(Carbon $scheduledAt): bool
    {
        return $scheduledAt->lte(now());
    }

    /**
     * Get available offset options.
     */
    public function getAvailableOffsets(): array
    {
        return config('reminders.offset_options', [
            '15 minutes',
            '30 minutes',
            '1 hour',
            '2 hours',
            '1 day',
            '2 days',
            '1 week',
        ]);
    }

    /**
     * Validate offset string.
     */
    public function isValidOffset(string $offset): bool
    {
        try {
            $this->parseOffset($offset);
            return true;
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * Get the next reminder time for recurring reminders.
     */
    public function getNextReminderTime(Carbon $currentTime, string $interval): Carbon
    {
        $offsetData = $this->parseOffset($interval);
        return $currentTime->copy()->add($offsetData['value'], $offsetData['unit']);
    }
}
