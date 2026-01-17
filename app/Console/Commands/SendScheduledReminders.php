<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use Illuminate\Console\Command;

class SendScheduledReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send
                            {--limit= : Limit the number of reminders to process}
                            {--dry-run : Run without actually sending reminders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send all pending reminders that are due';

    protected ReminderService $reminderService;

    /**
     * Create a new command instance.
     */
    public function __construct(ReminderService $reminderService)
    {
        parent::__construct();
        $this->reminderService = $reminderService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Processing pending reminders...');

        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No reminders will actually be sent');
        }

        // Get pending reminders
        $reminders = $this->reminderService->getPendingReminders();

        if ($this->option('limit')) {
            $reminders = $reminders->take((int) $this->option('limit'));
        }

        $total = $reminders->count();

        if ($total === 0) {
            $this->info('No pending reminders to send.');
            return self::SUCCESS;
        }

        $this->info("Found {$total} pending reminder(s) to send.");

        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();

        $sent = 0;
        $failed = 0;

        foreach ($reminders as $reminder) {
            if ($dryRun) {
                $this->line("\n[DRY RUN] Would send reminder #{$reminder->id} to user #{$reminder->user_id} via " . implode(', ', $reminder->channels));
            } else {
                if ($this->reminderService->sendReminder($reminder)) {
                    $sent++;
                } else {
                    $failed++;
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        if ($dryRun) {
            $this->info("DRY RUN completed. {$total} reminder(s) would have been processed.");
        } else {
            $this->info("Processing completed!");
            $this->table(
                ['Status', 'Count'],
                [
                    ['Sent', $sent],
                    ['Failed', $failed],
                    ['Total', $total],
                ]
            );
        }

        return self::SUCCESS;
    }
}
