# User Settings & Reminder System - Implementation Plan

## Overview

This plan outlines the implementation of a flexible user settings system with a comprehensive reminder feature for lawyers. Each lawyer can customize their reminder preferences including types, timing, channels, and timezone.

---

## 1. Database Schema

### 1.1 User Settings Table

**Table Name:** `user_settings`

```sql
CREATE TABLE user_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    key VARCHAR(255) NOT NULL,
    value JSON NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    UNIQUE KEY unique_user_setting (user_id, key),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_key (key)
);
```

**Purpose:** Store flexible key-value settings for each user.

**Example Data:**

```
user_id | key                | value
--------|--------------------|---------------------------------
1       | reminder_types     | ["session", "event", "order"]
1       | reminder_offset    | "1 day"
1       | reminder_channels  | ["email", "sms"]
1       | timezone           | "Africa/Cairo"
1       | notification_sound | true
1       | email_digest       | "daily"
```

### 1.2 Reminders Table

**Table Name:** `reminders`

```sql
CREATE TABLE reminders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    remindable_type VARCHAR(255) NOT NULL,
    remindable_id BIGINT UNSIGNED NOT NULL,
    reminder_type ENUM('session', 'event', 'order', 'payment', 'deadline') NOT NULL,
    scheduled_at TIMESTAMP NOT NULL,
    sent_at TIMESTAMP NULL,
    status ENUM('pending', 'sent', 'failed', 'cancelled') DEFAULT 'pending',
    channels JSON NOT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_remindable (remindable_type, remindable_id),
    INDEX idx_scheduled_at (scheduled_at),
    INDEX idx_status (status),
    INDEX idx_reminder_type (reminder_type)
);
```

**Purpose:** Store scheduled reminders for various entities.

### 1.3 Reminder Logs Table

**Table Name:** `reminder_logs`

```sql
CREATE TABLE reminder_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reminder_id BIGINT UNSIGNED NOT NULL,
    channel VARCHAR(50) NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    response TEXT NULL,
    error_message TEXT NULL,
    sent_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,

    FOREIGN KEY (reminder_id) REFERENCES reminders(id) ON DELETE CASCADE,
    INDEX idx_reminder_id (reminder_id),
    INDEX idx_channel (channel),
    INDEX idx_status (status)
);
```

**Purpose:** Track delivery status of reminders across different channels.

---

## 2. Models

### 2.1 UserSetting Model

**File:** `app/Models/UserSetting.php`

**Features:**

-   Belongs to User
-   JSON casting for value field
-   Helper methods for getting/setting specific settings
-   Scopes for filtering by key

### 2.2 Reminder Model

**File:** `app/Models/Reminder.php`

**Features:**

-   Belongs to User
-   Polymorphic relationship (remindable)
-   JSON casting for channels and metadata
-   Scopes for pending, sent, failed reminders
-   Methods for marking as sent/failed

### 2.3 ReminderLog Model

**File:** `app/Models/ReminderLog.php`

**Features:**

-   Belongs to Reminder
-   Track delivery attempts

---

## 3. Migrations

### Migration Files to Create:

1. **`2026_01_15_000001_create_user_settings_table.php`**

    - Create user_settings table
    - Add indexes

2. **`2026_01_15_000002_create_reminders_table.php`**

    - Create reminders table
    - Add indexes and foreign keys

3. **`2026_01_15_000003_create_reminder_logs_table.php`**

    - Create reminder_logs table
    - Add indexes and foreign keys

4. **`2026_01_15_000004_add_timezone_to_users_table.php`**
    - Add timezone column to users table (optional, can use settings instead)

---

## 4. Services

### 4.1 UserSettingsService

**File:** `app/Services/UserSettingsService.php`

**Methods:**

-   `getSetting($userId, $key, $default = null)`
-   `setSetting($userId, $key, $value)`
-   `getSettings($userId, array $keys = [])`
-   `setSettings($userId, array $settings)`
-   `deleteSetting($userId, $key)`
-   `getReminderPreferences($userId)`

### 4.2 ReminderService

**File:** `app/Services/ReminderService.php`

**Methods:**

-   `createReminder($userId, $remindable, $type, $scheduledAt, $channels = null)`
-   `scheduleRemindersForSession($session)`
-   `scheduleRemindersForEvent($event)`
-   `scheduleRemindersForPayment($payment)`
-   `cancelReminders($remindable)`
-   `rescheduleReminders($remindable, $newDateTime)`
-   `sendReminder($reminder)`
-   `sendViaEmail($reminder)`
-   `sendViaSms($reminder)`
-   `sendViaPush($reminder)`

### 4.3 ReminderScheduler

**File:** `app/Services/ReminderScheduler.php`

**Methods:**

-   `calculateReminderTime($eventDateTime, $offset)`
-   `parseOffset($offsetString)` (e.g., "1 day", "2 hours", "30 minutes")
-   `getUserTimezone($userId)`
-   `convertToUserTimezone($dateTime, $userId)`

---

## 5. Console Commands

### 5.1 SendScheduledReminders Command

**File:** `app/Console/Commands/SendScheduledReminders.php`

**Purpose:** Process and send pending reminders

**Schedule:** Every minute via Laravel Scheduler

**Logic:**

```php
- Fetch all pending reminders where scheduled_at <= now()
- For each reminder:
  - Get user's reminder channels
  - Send via each channel
  - Log results
  - Update reminder status
```

### 5.2 CleanupOldReminders Command

**File:** `app/Console/Commands/CleanupOldReminders.php`

**Purpose:** Clean up old sent/failed reminders

**Schedule:** Daily

---

## 6. Filament Resources

### 6.1 Settings Page

**File:** `app/Filament/Pages/Settings.php`

**Sections:**

#### A. Reminder Preferences Section

**Fields:**

1. **Reminder Types** (CheckboxList)

    - Session Reminders
    - Event Reminders
    - Payment Reminders
    - Deadline Reminders

2. **Default Reminder Offset** (Select)

    - 15 minutes before
    - 30 minutes before
    - 1 hour before
    - 2 hours before
    - 1 day before
    - 2 days before
    - 1 week before
    - Custom (with time input)

3. **Reminder Channels** (CheckboxList)

    - Email
    - SMS
    - Push Notification
    - In-App Notification

4. **Timezone** (Select)
    - Searchable dropdown with all timezones
    - Default: Africa/Cairo

#### B. Notification Preferences Section

**Fields:**

1. **Email Digest** (Select)

    - Real-time
    - Daily
    - Weekly
    - Disabled

2. **Notification Sound** (Toggle)

3. **Desktop Notifications** (Toggle)

#### C. Display Preferences Section

**Fields:**

1. **Language** (Select)

    - Arabic
    - English

2. **Date Format** (Select)

    - DD/MM/YYYY
    - MM/DD/YYYY
    - YYYY-MM-DD

3. **Time Format** (Select)
    - 12-hour
    - 24-hour

### 6.2 Reminder Resource (Admin View)

**File:** `app/Filament/Resources/ReminderResource.php`

**Purpose:** Allow admins to view and manage all reminders

**Features:**

-   List all reminders with filters
-   View reminder details
-   Manually trigger reminders
-   Cancel reminders
-   View delivery logs

---

## 7. Events & Listeners

### 7.1 Events to Create:

1. **`SessionCreated`** → Schedule session reminder
2. **`SessionUpdated`** → Reschedule session reminder
3. **`SessionCancelled`** → Cancel session reminder
4. **`EventCreated`** → Schedule event reminder
5. **`EventUpdated`** → Reschedule event reminder
6. **`PaymentDue`** → Schedule payment reminder
7. **`ReminderSent`** → Log reminder delivery

### 7.2 Listeners:

1. **`ScheduleSessionReminder`**
2. **`RescheduleSessionReminder`**
3. **`CancelSessionReminder`**
4. **`ScheduleEventReminder`**
5. **`LogReminderDelivery`**

---

## 8. API Endpoints (Optional - for mobile/external access)

### Settings Endpoints:

```
GET    /api/settings                  - Get all user settings
GET    /api/settings/{key}            - Get specific setting
POST   /api/settings                  - Update settings
DELETE /api/settings/{key}            - Delete setting
```

### Reminder Endpoints:

```
GET    /api/reminders                 - List user's reminders
GET    /api/reminders/{id}            - Get reminder details
POST   /api/reminders                 - Create manual reminder
PUT    /api/reminders/{id}            - Update reminder
DELETE /api/reminders/{id}            - Cancel reminder
```

---

## 9. Notification Templates

### 9.1 Email Templates

**Location:** `resources/views/emails/reminders/`

Templates needed:

-   `session-reminder.blade.php`
-   `event-reminder.blade.php`
-   `payment-reminder.blade.php`
-   `deadline-reminder.blade.php`

### 9.2 SMS Templates

**Location:** `app/Services/SmsTemplates.php`

Short message templates for each reminder type.

### 9.3 Push Notification Templates

**Location:** `app/Services/PushNotificationTemplates.php`

Title and body templates for push notifications.

---

## 10. Configuration

### 10.1 Config File

**File:** `config/reminders.php`

```php
return [
    'enabled' => env('REMINDERS_ENABLED', true),

    'default_offset' => '1 day',

    'default_channels' => ['email'],

    'available_channels' => [
        'email' => true,
        'sms' => env('SMS_ENABLED', false),
        'push' => env('PUSH_ENABLED', false),
        'in_app' => true,
    ],

    'reminder_types' => [
        'session',
        'event',
        'payment',
        'deadline',
    ],

    'offset_options' => [
        '15 minutes',
        '30 minutes',
        '1 hour',
        '2 hours',
        '1 day',
        '2 days',
        '1 week',
    ],

    'cleanup_after_days' => 30,
];
```

---

## 11. Localization

### 11.1 Translation Keys to Add

**File:** `lang/en.json` and `lang/ar.json`

```json
{
    "settings.title": "Settings",
    "settings.reminder_preferences": "Reminder Preferences",
    "settings.reminder_types": "Reminder Types",
    "settings.reminder_offset": "Default Reminder Time",
    "settings.reminder_channels": "Notification Channels",
    "settings.timezone": "Timezone",
    "settings.session_reminders": "Session Reminders",
    "settings.event_reminders": "Event Reminders",
    "settings.payment_reminders": "Payment Reminders",
    "settings.deadline_reminders": "Deadline Reminders",
    "settings.email_channel": "Email",
    "settings.sms_channel": "SMS",
    "settings.push_channel": "Push Notification",
    "settings.saved_successfully": "Settings saved successfully",

    "reminders.title": "Reminders",
    "reminders.scheduled_at": "Scheduled At",
    "reminders.status": "Status",
    "reminders.type": "Type",
    "reminders.channels": "Channels",
    "reminders.pending": "Pending",
    "reminders.sent": "Sent",
    "reminders.failed": "Failed",
    "reminders.cancelled": "Cancelled"
}
```

---

## 12. Testing

### 12.1 Unit Tests

-   UserSettingsService tests
-   ReminderService tests
-   ReminderScheduler tests

### 12.2 Feature Tests

-   Settings page functionality
-   Reminder creation and scheduling
-   Reminder sending via different channels
-   Event listeners

### 12.3 Integration Tests

-   End-to-end reminder flow
-   Multi-channel delivery

---

## 13. Implementation Order

### Phase 1: Database & Models (Day 1)

1. ✅ Create migrations
2. ✅ Run migrations
3. ✅ Create models with relationships
4. ✅ Create seeders for default settings

### Phase 2: Services (Day 2)

1. ✅ UserSettingsService
2. ✅ ReminderScheduler
3. ✅ ReminderService (basic)

### Phase 3: Filament Settings Page (Day 3)

1. ✅ Create Settings page
2. ✅ Add Reminder Preferences section
3. ✅ Add form validation
4. ✅ Add save functionality
5. ✅ Add translations

### Phase 4: Reminder Logic (Day 4)

1. ✅ Complete ReminderService
2. ✅ Create Events & Listeners
3. ✅ Integrate with existing resources (Sessions, Events, Payments)

### Phase 5: Notification Channels (Day 5)

1. ✅ Email notifications
2. ✅ SMS notifications (if enabled)
3. ✅ Push notifications (if enabled)
4. ✅ Create templates

### Phase 6: Console Commands (Day 6)

1. ✅ SendScheduledReminders command
2. ✅ CleanupOldReminders command
3. ✅ Register in Kernel

### Phase 7: Admin Interface (Day 7)

1. ✅ ReminderResource for admins
2. ✅ Reminder logs view
3. ✅ Manual reminder creation

### Phase 8: Testing & Polish (Day 8)

1. ✅ Write tests
2. ✅ Test all reminder flows
3. ✅ Fix bugs
4. ✅ Documentation

---

## 14. Additional Features (Future Enhancements)

1. **Recurring Reminders**

    - Weekly/monthly reminders
    - Custom recurrence patterns

2. **Smart Reminders**

    - AI-based reminder suggestions
    - Learn from user behavior

3. **Reminder Templates**

    - Custom message templates
    - Template variables

4. **Reminder Groups**

    - Group related reminders
    - Batch operations

5. **Reminder Analytics**
    - Delivery success rates
    - User engagement metrics

---

## 15. Dependencies

### Required Packages:

-   `filament/filament` (already installed)
-   `spatie/laravel-permission` (already installed)
-   SMS provider package (e.g., `twilio/sdk` or `nexmo/laravel`)
-   Push notification package (e.g., `laravel/firebase-cloud-messaging`)

### Optional Packages:

-   `nesbot/carbon` (for timezone handling - included in Laravel)
-   `spatie/laravel-event-sourcing` (for advanced event handling)

---

## 16. Security Considerations

1. **Authorization**

    - Users can only access their own settings
    - Admin role for viewing all reminders

2. **Validation**

    - Validate all setting values
    - Sanitize user inputs
    - Validate timezone values

3. **Rate Limiting**

    - Limit reminder creation
    - Prevent spam

4. **Data Privacy**
    - Encrypt sensitive settings
    - GDPR compliance for reminder data

---

## 17. Performance Optimization

1. **Database Indexes**

    - Index on user_id, scheduled_at, status
    - Composite indexes for common queries

2. **Caching**

    - Cache user settings
    - Cache timezone data

3. **Queue Jobs**

    - Queue reminder sending
    - Batch processing for multiple reminders

4. **Cleanup**
    - Regular cleanup of old reminders
    - Archive sent reminders

---

## Notes

-   All times should be stored in UTC in the database
-   Convert to user's timezone for display
-   Use Laravel's queue system for sending reminders
-   Implement retry logic for failed deliveries
-   Log all reminder activities for debugging
-   Consider implementing a "snooze" feature for reminders

---

**Created:** 2026-01-15
**Last Updated:** 2026-01-15
**Status:** Planning Phase
