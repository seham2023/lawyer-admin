<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Database\Seeder;

class UserSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();

        foreach ($users as $user) {
            // Set default reminder preferences for each user
            UserSetting::updateOrCreate(
                ['user_id' => $user->id, 'key' => 'reminder_types'],
                ['value' => ['session', 'event', 'order']]
            );

            UserSetting::updateOrCreate(
                ['user_id' => $user->id, 'key' => 'reminder_offset'],
                ['value' => '1 day']
            );

            UserSetting::updateOrCreate(
                ['user_id' => $user->id, 'key' => 'reminder_channels'],
                ['value' => ['email']]
            );

            UserSetting::updateOrCreate(
                ['user_id' => $user->id, 'key' => 'timezone'],
                ['value' => 'Africa/Cairo']
            );

            UserSetting::updateOrCreate(
                ['user_id' => $user->id, 'key' => 'notification_sound'],
                ['value' => true]
            );

            UserSetting::updateOrCreate(
                ['user_id' => $user->id, 'key' => 'email_digest'],
                ['value' => 'daily']
            );
        }

        $this->command->info('Default user settings created for ' . $users->count() . ' users.');
    }
}
