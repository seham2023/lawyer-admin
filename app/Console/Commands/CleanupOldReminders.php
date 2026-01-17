<?php

namespace App\Console\Commands;

use App\Models\Reminder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanupOldReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:cleanup
                            {--days= : Number of days to keep reminders (default from config)}
                            {--dry-run : Run without actually deleting reminders}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old sent and failed reminders';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = $this->option('days') ?? config('reminders.cleanup_after_days', 30);
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info("Cleaning up reminders older than {$days} days...");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No reminders will actually be deleted');
        }

        $cutoffDate = Carbon::now()->subDays($days);

        // Find old sent and failed reminders
        $query = Reminder::whereIn('status', ['sent', 'failed'])
            ->where('created_at', '<', $cutoffDate);

        $count = $query->count();

        if ($count === 0) {
            $this->info('No old reminders to clean up.');
            return self::SUCCESS;
        }

        $this->info("Found {$count} old reminder(s) to clean up.");

        // Confirm deletion unless force flag is used
        if (!$force && !$dryRun) {
            if (!$this->confirm("Are you sure you want to delete {$count} reminder(s)?")) {
                $this->info('Cleanup cancelled.');
                return self::SUCCESS;
            }
        }

        if ($dryRun) {
            $this->table(
                ['ID', 'Type', 'Status', 'Created At', 'Age (days)'],
                $query->limit(10)->get()->map(function ($reminder) {
                    return [
                        $reminder->id,
                        $reminder->reminder_type,
                        $reminder->status,
                        $reminder->created_at->toDateTimeString(),
                        $reminder->created_at->diffInDays(now()),
                    ];
                })->toArray()
            );

            if ($count > 10) {
                $this->info("... and " . ($count - 10) . " more.");
            }

            $this->info("DRY RUN completed. {$count} reminder(s) would have been deleted.");
        } else {
            $deleted = $query->delete();
            $this->info("Successfully deleted {$deleted} old reminder(s).");
        }

        return self::SUCCESS;
    }
}
