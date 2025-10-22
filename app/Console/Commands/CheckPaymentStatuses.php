<?php

namespace App\Console\Commands;

use App\Models\PaymentSession;
use App\Services\TabbyPaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckPaymentStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:check-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check statuses of pending payment sessions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking payment statuses...');

        // Get all pending payment sessions (created or link_sent)
        $pendingSessions = PaymentSession::whereIn('status', ['created', 'link_sent', 'pending'])
            ->where('provider', 'tabby')
            ->get();

        $this->info("Found {$pendingSessions->count()} pending payment sessions");

        $tabbyService = new TabbyPaymentService();
        $updatedCount = 0;

        foreach ($pendingSessions as $session) {
            $this->info("Checking session: {$session->session_id}");

            $result = $tabbyService->getPaymentStatus($session->payment_id);

            if ($result['success']) {
                $this->info("Payment {$session->payment_id} status: {$result['status']}");
                
                // Update the session status in the database
                $session->update([
                    'status' => $result['status'],
                    'response_data' => array_merge($session->response_data ?? [], $result['data'])
                ]);
                
                $updatedCount++;
            } else {
                $this->error("Failed to get status for payment {$session->payment_id}: " . $result['error']);
                
                Log::error('Failed to get payment status', [
                    'payment_id' => $session->payment_id,
                    'error' => $result['error'],
                ]);
            }
        }

        $this->info("Updated {$updatedCount} payment session statuses");
        
        return Command::SUCCESS;
    }
}
