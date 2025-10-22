<?php

namespace App\Services;

interface PaymentServiceInterface
{
    /**
     * Create a payment session
     *
     * @param float $amount
     * @param string $currency
     * @param string $buyerPhone
     * @param string $orderReferenceId
     * @param array $items
     * @param string|null $merchantCode
     * @return array
     */
    public function createSession(float $amount, string $currency, string $buyerPhone, string $orderReferenceId, array $items, string $merchantCode = null): array;

    /**
     * Send payment link to customer
     *
     * @param string $sessionId
     * @return bool
     */
    public function sendPaymentLink(string $sessionId): bool;

    /**
     * Get payment status
     *
     * @param string $paymentId
     * @return array
     */
    public function getPaymentStatus(string $paymentId): array;

    /**
     * Cancel a payment session
     *
     * @param string $sessionId
     * @return bool
     */
    public function cancelSession(string $sessionId): bool;
}