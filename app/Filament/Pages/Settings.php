<?php

namespace App\Filament\Pages;

use App\Services\UserSettingsService;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Settings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.settings';

    protected static ?int $navigationSort = 100;

    public ?array $data = [];

    protected UserSettingsService $settingsService;

    public function boot(UserSettingsService $settingsService): void
    {
        $this->settingsService = $settingsService;
    }

    public static function getNavigationLabel(): string
    {
        return __('settings.title');
    }

    public function getTitle(): string
    {
        return __('settings.title');
    }

    public function mount(): void
    {
        $userId = Auth::id();
        $settings = $this->settingsService->getAllSettings($userId);

        // Load current settings or use defaults
        $this->form->fill([
            'reminder_types' => $settings['reminder_types'] ?? ['session', 'event', 'order'],
            'reminder_offset' => $settings['reminder_offset'] ?? '1 day',
            'reminder_channels' => $settings['reminder_channels'] ?? ['email'],
            'timezone' => $settings['timezone'] ?? 'Africa/Cairo',
            'notification_sound' => $settings['notification_sound'] ?? true,
            'email_digest' => $settings['email_digest'] ?? 'daily',
            'date_format' => $settings['date_format'] ?? 'd/m/Y',
            'time_format' => $settings['time_format'] ?? '12',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Reminder Preferences Section
                Section::make(__('settings.reminder_preferences'))
                    ->description(__('settings.reminder_preferences_description'))
                    ->schema([
                        CheckboxList::make('reminder_types')
                            ->label(__('settings.reminder_types'))
                            ->options([
                                'session' => __('settings.session_reminders'),
                                'event' => __('settings.event_reminders'),
                                'order' => __('settings.order_reminders'),
                                'payment' => __('settings.payment_reminders'),
                                'deadline' => __('settings.deadline_reminders'),
                            ])
                            ->columns(2)
                            ->required()
                            ->helperText(__('settings.reminder_types_help')),

                        Select::make('reminder_offset')
                            ->label(__('settings.reminder_offset'))
                            ->options([
                                '15 minutes' => __('settings.offset_15_minutes'),
                                '30 minutes' => __('settings.offset_30_minutes'),
                                '1 hour' => __('settings.offset_1_hour'),
                                '2 hours' => __('settings.offset_2_hours'),
                                '4 hours' => __('settings.offset_4_hours'),
                                '1 day' => __('settings.offset_1_day'),
                                '2 days' => __('settings.offset_2_days'),
                                '3 days' => __('settings.offset_3_days'),
                                '1 week' => __('settings.offset_1_week'),
                            ])
                            ->required()
                            ->helperText(__('settings.reminder_offset_help')),

                        CheckboxList::make('reminder_channels')
                            ->label(__('settings.reminder_channels'))
                            ->options([
                                'email' => __('settings.email_channel'),
                                'sms' => __('settings.sms_channel'),
                                'push' => __('settings.push_channel'),
                                'in_app' => __('settings.in_app_channel'),
                            ])
                            ->columns(2)
                            ->required()
                            ->helperText(__('settings.reminder_channels_help')),

                        Select::make('timezone')
                            ->label(__('settings.timezone'))
                            ->options($this->getTimezoneOptions())
                            ->searchable()
                            ->required()
                            ->helperText(__('settings.timezone_help')),
                    ])
                    ->columns(1),

                // Notification Preferences Section
                Section::make(__('settings.notification_preferences'))
                    ->description(__('settings.notification_preferences_description'))
                    ->schema([
                        Select::make('email_digest')
                            ->label(__('settings.email_digest'))
                            ->options([
                                'realtime' => __('settings.digest_realtime'),
                                'daily' => __('settings.digest_daily'),
                                'weekly' => __('settings.digest_weekly'),
                                'disabled' => __('settings.digest_disabled'),
                            ])
                            ->required()
                            ->helperText(__('settings.email_digest_help')),

                        Toggle::make('notification_sound')
                            ->label(__('settings.notification_sound'))
                            ->helperText(__('settings.notification_sound_help')),
                    ])
                    ->columns(2),

                // Display Preferences Section
                Section::make(__('settings.display_preferences'))
                    ->description(__('settings.display_preferences_description'))
                    ->schema([
                        Select::make('date_format')
                            ->label(__('settings.date_format'))
                            ->options([
                                'd/m/Y' => 'DD/MM/YYYY (15/01/2026)',
                                'm/d/Y' => 'MM/DD/YYYY (01/15/2026)',
                                'Y-m-d' => 'YYYY-MM-DD (2026-01-15)',
                            ])
                            ->required(),

                        Select::make('time_format')
                            ->label(__('settings.time_format'))
                            ->options([
                                '12' => __('settings.time_12_hour'),
                                '24' => __('settings.time_24_hour'),
                            ])
                            ->required(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $userId = Auth::id();

        // Save all settings
        $this->settingsService->setSettings($userId, $data);

        Notification::make()
            ->success()
            ->title(__('settings.saved_successfully'))
            ->body(__('settings.saved_successfully_message'))
            ->send();
    }

    protected function getTimezoneOptions(): array
    {
        $timezones = [
            // Africa
            'Africa/Cairo' => 'Africa/Cairo (GMT+2)',
            'Africa/Casablanca' => 'Africa/Casablanca (GMT+1)',
            'Africa/Johannesburg' => 'Africa/Johannesburg (GMT+2)',
            'Africa/Lagos' => 'Africa/Lagos (GMT+1)',
            'Africa/Nairobi' => 'Africa/Nairobi (GMT+3)',

            // Asia
            'Asia/Dubai' => 'Asia/Dubai (GMT+4)',
            'Asia/Riyadh' => 'Asia/Riyadh (GMT+3)',
            'Asia/Kuwait' => 'Asia/Kuwait (GMT+3)',
            'Asia/Bahrain' => 'Asia/Bahrain (GMT+3)',
            'Asia/Qatar' => 'Asia/Qatar (GMT+3)',
            'Asia/Jerusalem' => 'Asia/Jerusalem (GMT+2)',
            'Asia/Beirut' => 'Asia/Beirut (GMT+2)',
            'Asia/Amman' => 'Asia/Amman (GMT+2)',
            'Asia/Baghdad' => 'Asia/Baghdad (GMT+3)',
            'Asia/Tehran' => 'Asia/Tehran (GMT+3:30)',
            'Asia/Karachi' => 'Asia/Karachi (GMT+5)',
            'Asia/Kolkata' => 'Asia/Kolkata (GMT+5:30)',
            'Asia/Singapore' => 'Asia/Singapore (GMT+8)',
            'Asia/Tokyo' => 'Asia/Tokyo (GMT+9)',

            // Europe
            'Europe/London' => 'Europe/London (GMT+0)',
            'Europe/Paris' => 'Europe/Paris (GMT+1)',
            'Europe/Berlin' => 'Europe/Berlin (GMT+1)',
            'Europe/Rome' => 'Europe/Rome (GMT+1)',
            'Europe/Madrid' => 'Europe/Madrid (GMT+1)',
            'Europe/Istanbul' => 'Europe/Istanbul (GMT+3)',
            'Europe/Moscow' => 'Europe/Moscow (GMT+3)',

            // Americas
            'America/New_York' => 'America/New_York (GMT-5)',
            'America/Chicago' => 'America/Chicago (GMT-6)',
            'America/Denver' => 'America/Denver (GMT-7)',
            'America/Los_Angeles' => 'America/Los_Angeles (GMT-8)',
            'America/Toronto' => 'America/Toronto (GMT-5)',
            'America/Mexico_City' => 'America/Mexico_City (GMT-6)',
            'America/Sao_Paulo' => 'America/Sao_Paulo (GMT-3)',

            // Australia
            'Australia/Sydney' => 'Australia/Sydney (GMT+11)',
            'Australia/Melbourne' => 'Australia/Melbourne (GMT+11)',
            'Australia/Perth' => 'Australia/Perth (GMT+8)',

            // UTC
            'UTC' => 'UTC (GMT+0)',
        ];

        return $timezones;
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label(__('settings.save'))
                ->submit('save')
                ->color('primary'),
        ];
    }
}
