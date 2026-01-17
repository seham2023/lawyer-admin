<?php

namespace App\Services;

use App\Models\UserSetting;
use Illuminate\Support\Collection;

class UserSettingsService
{
    /**
     * Get a single setting value for a user.
     */
    public function getSetting(int $userId, string $key, $default = null)
    {
        return UserSetting::getValue($userId, $key, $default);
    }

    /**
     * Set a single setting value for a user.
     */
    public function setSetting(int $userId, string $key, $value): UserSetting
    {
        return UserSetting::setValue($userId, $key, $value);
    }

    /**
     * Get multiple settings for a user.
     * If keys array is empty, returns all settings.
     */
    public function getSettings(int $userId, array $keys = []): Collection
    {
        $query = UserSetting::forUser($userId);

        if (!empty($keys)) {
            $query->whereIn('key', $keys);
        }

        return $query->get()->pluck('value', 'key');
    }

    /**
     * Set multiple settings for a user.
     */
    public function setSettings(int $userId, array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->setSetting($userId, $key, $value);
        }
    }

    /**
     * Delete a setting for a user.
     */
    public function deleteSetting(int $userId, string $key): bool
    {
        return UserSetting::deleteSetting($userId, $key);
    }

    /**
     * Get reminder preferences for a user.
     */
    public function getReminderPreferences(int $userId): array
    {
        $settings = $this->getSettings($userId, [
            'reminder_types',
            'reminder_offset',
            'reminder_channels',
            'timezone',
        ]);

        return [
            'reminder_types' => $settings->get('reminder_types', config('reminders.reminder_types', ['session', 'event', 'order'])),
            'reminder_offset' => $settings->get('reminder_offset', config('reminders.default_offset', '1 day')),
            'reminder_channels' => $settings->get('reminder_channels', config('reminders.default_channels', ['email'])),
            'timezone' => $settings->get('timezone', config('app.timezone', 'UTC')),
        ];
    }

    /**
     * Set reminder preferences for a user.
     */
    public function setReminderPreferences(int $userId, array $preferences): void
    {
        $allowedKeys = ['reminder_types', 'reminder_offset', 'reminder_channels', 'timezone'];

        foreach ($preferences as $key => $value) {
            if (in_array($key, $allowedKeys)) {
                $this->setSetting($userId, $key, $value);
            }
        }
    }

    /**
     * Get all settings for a user as an associative array.
     */
    public function getAllSettings(int $userId): array
    {
        return $this->getSettings($userId)->toArray();
    }

    /**
     * Check if a user has a specific setting.
     */
    public function hasSetting(int $userId, string $key): bool
    {
        return UserSetting::forUser($userId)->byKey($key)->exists();
    }

    /**
     * Reset all settings for a user.
     */
    public function resetAllSettings(int $userId): bool
    {
        return UserSetting::forUser($userId)->delete();
    }

    /**
     * Get user's timezone.
     */
    public function getUserTimezone(int $userId): string
    {
        return $this->getSetting($userId, 'timezone', config('app.timezone', 'UTC'));
    }
}
