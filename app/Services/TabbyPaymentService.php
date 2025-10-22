<?php

namespace App\Services;

use App\Models\PaymentSession;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TabbyPaymentService extends AbstractPaymentService
{
    protected string $providerName = 'tabby';

    private string $apiBaseUrl;
    private string $apiKey;
    private string $merchantCode;

    public function __construct()
    {
        parent::__construct();
        
        $this->apiBaseUrl = $this->config['api_base_url'] ?? config('payment.tabby.api_base_url', 'https://api.tabby.ai');
        $this->apiKey = $this->config['api_key'] ?? config('payment.tabby.api_key');
        $this->merchantCode = $this->config['merchant_code'] ?? config('payment.tabby.merchant_code');
    }

    /**
     * Create a Tabby payment session
     *
     * @param float $amount
     * @param string $currency
     * @param string $buyerPhone
     * @param string $orderReferenceId
     * @param array $items
     * @param string|null $merchantCode
     * @return array
     */
    public function createSession(float $amount, string $currency, string $buyerPhone, string $orderReferenceId, array $items, string $merchantCode = null): array
    {
        try {
            $payload = [
                'payment' => [
                    'amount' => $this->formatAmount($amount, $currency),
                    'currency' => $currency,
                    'buyer' => [
                        'phone' => $buyerPhone,
                    ],
                    'order' => [
                        'reference_id' => $orderReferenceId,
                        'items' => $items,
                    ],
                ],
                'merchant_code' => $merchantCode ?? $this->merchantCode,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->apiBaseUrl . '/api/v2/checkout', $payload);

            if (!$response->successful()) {
                Log::error('Tabby create session failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'payload' => $payload
                ]);

                return [
                    'success' => false,
                    'error' => 'Failed to create Tabby session: ' . $response->body(),
                    'status' => null,
                    'session_id' => null,
                    'payment_id' => null,
                ];
            }

            $responseData = $response->json();

            // Save the payment session to database
            $paymentSession = $this->savePaymentSession([
                'session_id' => $responseData['id'] ?? null,
                'payment_id' => $responseData['payment']['id'] ?? null,
                'provider' => 'tabby',
                'status' => $responseData['status'] ?? 'unknown',
                'amount' => $amount,
                'currency' => $currency,
                'buyer_phone' => $buyerPhone,
                'order_reference_id' => $orderReferenceId,
                'merchant_code' => $merchantCode ?? $this->merchantCode,
                'web_url' => $responseData['web_url'] ?? null,
                'response_data' => $responseData,
            ]);

            return [
                'success' => true,
                'status' => $responseData['status'],
                'session_id' => $responseData['id'],
                'payment_id' => $responseData['payment']['id'],
                'web_url' => $responseData['web_url'] ?? null,
                'payment_session' => $paymentSession,
            ];
        } catch (\Exception $e) {
            Log::error('Exception in Tabby create session', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Exception occurred while creating Tabby session: ' . $e->getMessage(),
                'status' => null,
                'session_id' => null,
                'payment_id' => null,
            ];
        }
    }

    /**
     * Send payment link to customer via SMS
     *
     * @param string $sessionId
     * @return bool
     */
    public function sendPaymentLink(string $sessionId): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->apiBaseUrl . "/api/v2/checkout/{$sessionId}/send_hpp_link");

            if (!$response->successful()) {
                Log::error('Tabby send payment link failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'session_id' => $sessionId
                ]);

                return false;
            }

            // Update the session status after sending the link
            $this->updatePaymentSessionStatus($sessionId, 'link_sent', $response->json());

            return true;
        } catch (\Exception $e) {
            Log::error('Exception in Tabby send payment link', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'session_id' => $sessionId,
            ]);

            return false;
        }
    }

    /**
     * Get payment status
     *
     * @param string $paymentId
     * @return array
     */
    public function getPaymentStatus(string $paymentId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get($this->apiBaseUrl . "/api/v2/payments/{$paymentId}");

            if (!$response->successful()) {
                Log::error('Tabby get payment status failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'payment_id' => $paymentId
                ]);

                return [
                    'success' => false,
                    'error' => 'Failed to get payment status: ' . $response->body(),
                    'status' => null,
                ];
            }

            $responseData = $response->json();
            $status = $responseData['status'] ?? null;

            // Find the payment session by payment_id and update its status
            $session = PaymentSession::where('payment_id', $paymentId)->first();
            if ($session) {
                $this->updatePaymentSessionStatus($session->session_id, $status, $responseData);
            }

            return [
                'success' => true,
                'status' => $status,
                'data' => $responseData,
            ];
        } catch (\Exception $e) {
            Log::error('Exception in Tabby get payment status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payment_id' => $paymentId,
            ]);

            return [
                'success' => false,
                'error' => 'Exception occurred while getting payment status: ' . $e->getMessage(),
                'status' => null,
            ];
        }
    }

    /**
     * Cancel a payment session
     *
     * @param string $sessionId
     * @return bool
     */
    public function cancelSession(string $sessionId): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->apiBaseUrl . "/api/v2/checkout/{$sessionId}/cancel");

            if (!$response->successful()) {
                // Check if the error is because the session is already finalized
                $responseData = $response->json();
                if (isset($responseData['errorType']) && $responseData['errorType'] === 'bad_data' && 
                    isset($responseData['error']) && $responseData['error'] === 'session is finalized') {
                    
                    // Even if the session is finalized, we should check the status
                    $paymentSession = $this->getPaymentSession($sessionId);
                    if ($paymentSession && $paymentSession->payment_id) {
                        $statusResult = $this->getPaymentStatus($paymentSession->payment_id);
                        if ($statusResult['success'] && in_array(strtoupper($statusResult['status']), ['AUTHORIZED', 'CLOSED'])) {
                            // If payment was already authorized/closed, update our record
                            $this->updatePaymentSessionStatus($sessionId, $statusResult['status']);
                            return true; // Consider this a success since payment was already processed
                        }
                    }
                }

                Log::error('Tabby cancel session failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'session_id' => $sessionId
                ]);

                return false;
            }

            $responseData = $response->json();
            $status = $responseData['status'] ?? 'expired';

            // Update the session status after cancellation
            $this->updatePaymentSessionStatus($sessionId, $status, $responseData);

            return true;
        } catch (\Exception $e) {
            Log::error('Exception in Tabby cancel session', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'session_id' => $sessionId,
            ]);

            return false;
        }
    }
}
