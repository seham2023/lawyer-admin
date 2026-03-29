<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class PaymentController extends Controller
{
    protected function getPaymentOrFail(int|string $paymentId): Payment
    {
        return Payment::with(['payable', 'currency', 'status', 'payMethod', 'client'])
            ->findOrFail($paymentId);
    }

    protected function signedPaymentUrls(Payment $payment): array
    {
        return [
            'successUrl' => URL::signedRoute('payment.success', ['paymentId' => $payment->id]),
            'pendingUrl' => URL::signedRoute('payment.pending', ['paymentId' => $payment->id]),
            'failedUrl' => URL::signedRoute('payment.failed', ['paymentId' => $payment->id]),
            'statusUrl' => URL::signedRoute('payment.status', ['paymentId' => $payment->id]),
        ];
    }

    /**
     * Display payment success page after successful payment
     */
    public function success(Request $request, $paymentId)
    {
        try {
            $payment = $this->getPaymentOrFail($paymentId);

            Log::info('Payment success page accessed', [
                'payment_id' => $paymentId,
                'user_id' => auth()->id(),
            ]);

            return view('payments.success', [
                'payment' => $payment,
                ...$this->signedPaymentUrls($payment),
            ]);
        } catch (\Exception $e) {
            Log::error('Error accessing payment success page', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('payment.error')
                ->with('error', 'لم نتمكن من العثور على معلومات الدفع');
        }
    }

    /**
     * Display payment pending page
     */
    public function pending(Request $request, $paymentId)
    {
        try {
            $payment = $this->getPaymentOrFail($paymentId);

            Log::info('Payment pending page accessed', [
                'payment_id' => $paymentId,
                'user_id' => auth()->id(),
            ]);

            return view('payments.pending', [
                'payment' => $payment,
                ...$this->signedPaymentUrls($payment),
            ]);
        } catch (\Exception $e) {
            Log::error('Error accessing payment pending page', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('payment.error')
                ->with('error', 'لم نتمكن من العثور على معلومات الدفع');
        }
    }

    /**
     * Display payment failed page
     */
    public function failed(Request $request, $paymentId)
    {
        try {
            $payment = $this->getPaymentOrFail($paymentId);

            Log::info('Payment failed page accessed', [
                'payment_id' => $paymentId,
                'user_id' => auth()->id(),
            ]);

            return view('payments.failed', [
                'payment' => $payment,
                ...$this->signedPaymentUrls($payment),
            ]);
        } catch (\Exception $e) {
            Log::error('Error accessing payment failed page', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('payment.error')
                ->with('error', 'لم نتمكن من العثور على معلومات الدفع');
        }
    }

    /**
     * Display general payment error page
     */
    public function error(Request $request)
    {
        return view('payments.error', [
            'errorMessage' => $request->session()->get('error', 'حدث خطأ أثناء معالجة الدفع'),
        ]);
    }

    /**
     * Check payment status (AJAX endpoint)
     */
    public function checkStatus(Request $request, $paymentId)
    {
        try {
            $payment = Payment::with(['status'])->findOrFail($paymentId);

            return response()->json([
                'success' => true,
                'status' => $payment->status->name ?? 'unknown',
                'status_id' => $payment->status_id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 404);
        }
    }
}
