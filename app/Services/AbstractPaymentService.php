<?php

namespace App\Services;

use App\Models\PaymentSession;

abstract class AbstractPaymentService implements PaymentServiceInterface
{
    protected string $providerName;
    protected array $config;

    public function __construct()
    {
        $this->config = config('payment.providers.' . $this->providerName);
    }

    /**
     * Save payment session to database
     *
     * @param array $data
     * @return PaymentSession
     */
    protected function savePaymentSession(array $data): PaymentSession
    {
        return PaymentSession::create($data);
    }

    /**
     * Update payment session status
     *
     * @param string $sessionId
     * @param string $status
     * @param array|null $responseData
     * @return bool
     */
    protected function updatePaymentSessionStatus(string $sessionId, string $status, array $responseData = null): bool
    {
        $session = PaymentSession::where('session_id', $sessionId)->first();
        
        if (!$session) {
            return false;
        }

        $updateData = ['status' => $status];
        
        if ($responseData) {
            $updateData['response_data'] = array_merge($session->response_data ?? [], $responseData);
        }

        $session->update($updateData);
        
        return true;
    }

    /**
     * Get payment session by session ID
     *
     * @param string $sessionId
     * @return PaymentSession|null
     */
    protected function getPaymentSession(string $sessionId): ?PaymentSession
    {
        return PaymentSession::where('session_id', $sessionId)->first();
    }

    /**
     * Format amount to required decimal places based on currency
     *
     * @param float $amount
     * @param string $currency
     * @return string
     */
    protected function formatAmount(float $amount, string $currency): string
    {
        // SAR and AED have 2 decimal places, KWD has 3
        $decimals = ($currency === 'KWD') ? 3 : 2;
        return number_format($amount, $decimals, '.', '');
    }
}
