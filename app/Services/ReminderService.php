<?php

namespace App\Services;

use App\Models\Reminder;
use App\Models\ReminderLog;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class ReminderService
{
    protected UserSettingsService $settingsService;
    protected ReminderScheduler $scheduler;

    public function __construct(UserSettingsService $settingsService, ReminderScheduler $scheduler)
    {
        $this->settingsService = $settingsService;
        $this->scheduler = $scheduler;
    }

    /**
     * Create a reminder for a user.
     */
    public function createReminder(
        int $userId,
        Model $remindable,
        string $type,
        $scheduledAt,
        ?array $channels = null,
        ?array $metadata = null
    ): Reminder {
        // Get user's reminder preferences if channels not specified
        if ($channels === null) {
            $preferences = $this->settingsService->getReminderPreferences($userId);
            $channels = $preferences['reminder_channels'];
        }

        // Ensure scheduled_at is a Carbon instance in UTC
        $scheduledAt = Carbon::parse($scheduledAt)->utc();

        return Reminder::create([
            'user_id' => $userId,
            'remindable_type' => get_class($remindable),
            'remindable_id' => $remindable->id,
            'reminder_type' => $type,
            'scheduled_at' => $scheduledAt,
            'channels' => $channels,
            'metadata' => $metadata,
            'status' => 'pending',
        ]);
    }

    /**
     * Schedule reminders for a session.
     */
    public function scheduleRemindersForSession($session): ?Reminder
    {
        $userId = $session->case_record->user_id ?? null;

        if (!$userId || !$session->session_date) {
            return null;
        }

        // Check if user has session reminders enabled
        $preferences = $this->settingsService->getReminderPreferences($userId);

        if (!in_array('session', $preferences['reminder_types'])) {
            return null;
        }

        // Calculate reminder time
        $scheduledAt = $this->scheduler->calculateReminderTime(
            $session->session_date,
            $preferences['reminder_offset']
        );

        // Don't create reminder if it's in the past
        if ($scheduledAt->isPast()) {
            return null;
        }

        return $this->createReminder(
            $userId,
            $session,
            'session',
            $scheduledAt,
            $preferences['reminder_channels'],
            [
                'session_number' => $session->session_number,
                'session_date' => $session->session_date->toDateTimeString(),
                'case_number' => $session->case_record->case_number ?? null,
            ]
        );
    }

    /**
     * Schedule reminders for an event.
     */
    public function scheduleRemindersForEvent($event): ?Reminder
    {
        $userId = $event->user_id ?? null;

        if (!$userId || !$event->event_date) {
            return null;
        }

        // Check if user has event reminders enabled
        $preferences = $this->settingsService->getReminderPreferences($userId);

        if (!in_array('event', $preferences['reminder_types'])) {
            return null;
        }

        // Calculate reminder time
        $scheduledAt = $this->scheduler->calculateReminderTime(
            $event->event_date,
            $preferences['reminder_offset']
        );

        // Don't create reminder if it's in the past
        if ($scheduledAt->isPast()) {
            return null;
        }

        return $this->createReminder(
            $userId,
            $event,
            'event',
            $scheduledAt,
            $preferences['reminder_channels'],
            [
                'event_title' => $event->title,
                'event_date' => $event->event_date->toDateTimeString(),
            ]
        );
    }

    /**
     * Schedule reminders for a payment.
     */
    public function scheduleRemindersForPayment($payment): ?Reminder
    {
        $userId = $payment->case_record->user_id ?? null;

        if (!$userId || !$payment->due_date) {
            return null;
        }

        // Check if user has payment reminders enabled
        $preferences = $this->settingsService->getReminderPreferences($userId);

        if (!in_array('payment', $preferences['reminder_types'])) {
            return null;
        }

        // Calculate reminder time
        $scheduledAt = $this->scheduler->calculateReminderTime(
            $payment->due_date,
            $preferences['reminder_offset']
        );

        // Don't create reminder if it's in the past
        if ($scheduledAt->isPast()) {
            return null;
        }

        return $this->createReminder(
            $userId,
            $payment,
            'payment',
            $scheduledAt,
            $preferences['reminder_channels'],
            [
                'amount' => $payment->amount,
                'due_date' => $payment->due_date->toDateTimeString(),
                'case_number' => $payment->case_record->case_number ?? null,
            ]
        );
    }

    /**
     * Cancel all reminders for a remindable entity.
     */
    public function cancelReminders(Model $remindable): int
    {
        return Reminder::where('remindable_type', get_class($remindable))
            ->where('remindable_id', $remindable->id)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);
    }

    /**
     * Reschedule reminders for a remindable entity.
     */
    public function rescheduleReminders(Model $remindable, $newDateTime): void
    {
        $reminders = Reminder::where('remindable_type', get_class($remindable))
            ->where('remindable_id', $remindable->id)
            ->where('status', 'pending')
            ->get();

        foreach ($reminders as $reminder) {
            $preferences = $this->settingsService->getReminderPreferences($reminder->user_id);

            $newScheduledAt = $this->scheduler->calculateReminderTime(
                $newDateTime,
                $preferences['reminder_offset']
            );

            // Cancel if new time is in the past, otherwise reschedule
            if ($newScheduledAt->isPast()) {
                $reminder->markAsCancelled();
            } else {
                $reminder->update(['scheduled_at' => $newScheduledAt]);
            }
        }
    }

    /**
     * Send a reminder through all configured channels.
     */
    public function sendReminder(Reminder $reminder): bool
    {
        if (!$reminder->canBeSent()) {
            Log::warning("Reminder {$reminder->id} cannot be sent. Status: {$reminder->status}");
            return false;
        }

        $channels = $reminder->channels ?? [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($channels as $channel) {
            try {
                $result = match ($channel) {
                    'email' => $this->sendViaEmail($reminder),
                    'sms' => $this->sendViaSms($reminder),
                    'push' => $this->sendViaPush($reminder),
                    default => false,
                };

                if ($result) {
                    ReminderLog::logSuccess($reminder->id, $channel);
                    $successCount++;
                } else {
                    ReminderLog::logFailure($reminder->id, $channel, 'Unknown error');
                    $failureCount++;
                }
            } catch (Exception $e) {
                ReminderLog::logFailure($reminder->id, $channel, $e->getMessage());
                $failureCount++;
                Log::error("Failed to send reminder {$reminder->id} via {$channel}: " . $e->getMessage());
            }
        }

        // Mark as sent if at least one channel succeeded
        if ($successCount > 0) {
            $reminder->markAsSent();
            return true;
        } else {
            $reminder->markAsFailed();
            return false;
        }
    }

    /**
     * Send reminder via email.
     */
    protected function sendViaEmail(Reminder $reminder): bool
    {
        try {
            $user = $reminder->user;
            $remindable = $reminder->remindable;

            // TODO: Implement email sending logic
            // Mail::to($user->email)->send(new ReminderMail($reminder));

            Log::info("Email reminder sent to {$user->email} for reminder {$reminder->id}");
            return true;
        } catch (Exception $e) {
            Log::error("Email sending failed for reminder {$reminder->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send reminder via SMS.
     */
    protected function sendViaSms(Reminder $reminder): bool
    {
        try {
            // TODO: Implement SMS sending logic
            // This would integrate with Twilio, Nexmo, or other SMS providers

            Log::info("SMS reminder sent for reminder {$reminder->id}");
            return true;
        } catch (Exception $e) {
            Log::error("SMS sending failed for reminder {$reminder->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send reminder via push notification.
     */
    protected function sendViaPush(Reminder $reminder): bool
    {
        try {
            // TODO: Implement push notification logic
            // This would integrate with FCM or other push services

            Log::info("Push notification sent for reminder {$reminder->id}");
            return true;
        } catch (Exception $e) {
            Log::error("Push notification failed for reminder {$reminder->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get pending reminders that are due to be sent.
     */
    public function getPendingReminders(): \Illuminate\Database\Eloquent\Collection
    {
        return Reminder::pending()
            ->scheduledBefore(now())
            ->with(['user', 'remindable'])
            ->get();
    }

    /**
     * Process all pending reminders.
     */
    public function processPendingReminders(): array
    {
        $reminders = $this->getPendingReminders();
        $results = [
            'total' => $reminders->count(),
            'sent' => 0,
            'failed' => 0,
        ];

        foreach ($reminders as $reminder) {
            if ($this->sendReminder($reminder)) {
                $results['sent']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }
}
