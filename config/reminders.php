<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Reminders Enabled
    |--------------------------------------------------------------------------
    |
    | This option controls whether the reminder system is enabled globally.
    |
    */
    'enabled' => env('REMINDERS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Default Reminder Offset
    |--------------------------------------------------------------------------
    |
    | The default time before an event when a reminder should be sent.
    |
    */
    'default_offset' => env('REMINDERS_DEFAULT_OFFSET', '1 day'),

    /*
    |--------------------------------------------------------------------------
    | Default Notification Channels
    |--------------------------------------------------------------------------
    |
    | The default channels through which reminders will be sent.
    |
    */
    'default_channels' => ['email'],

    /*
    |--------------------------------------------------------------------------
    | Available Channels
    |--------------------------------------------------------------------------
    |
    | Configure which notification channels are available in the system.
    |
    */
    'available_channels' => [
        'email' => true,
        'sms' => env('SMS_ENABLED', false),
        'push' => env('PUSH_ENABLED', false),
        'in_app' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Reminder Types
    |--------------------------------------------------------------------------
    |
    | The types of reminders that can be created in the system.
    |
    */
    'reminder_types' => [
        'session',
        'event',
        'order',
        'payment',
        'deadline',
    ],

    /*
    |--------------------------------------------------------------------------
    | Offset Options
    |--------------------------------------------------------------------------
    |
    | Predefined offset options that users can choose from.
    |
    */
    'offset_options' => [
        '15 minutes',
        '30 minutes',
        '1 hour',
        '2 hours',
        '4 hours',
        '1 day',
        '2 days',
        '3 days',
        '1 week',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cleanup After Days
    |--------------------------------------------------------------------------
    |
    | Number of days after which sent/failed reminders should be cleaned up.
    |
    */
    'cleanup_after_days' => env('REMINDERS_CLEANUP_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Batch Size
    |--------------------------------------------------------------------------
    |
    | Number of reminders to process in each batch when sending.
    |
    */
    'batch_size' => env('REMINDERS_BATCH_SIZE', 100),

    /*
    |--------------------------------------------------------------------------
    | Retry Failed Reminders
    |--------------------------------------------------------------------------
    |
    | Whether to retry sending failed reminders.
    |
    */
    'retry_failed' => env('REMINDERS_RETRY_FAILED', true),

    /*
    |--------------------------------------------------------------------------
    | Max Retry Attempts
    |--------------------------------------------------------------------------
    |
    | Maximum number of times to retry sending a failed reminder.
    |
    */
    'max_retry_attempts' => env('REMINDERS_MAX_RETRIES', 3),
];
