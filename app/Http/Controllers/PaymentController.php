<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Display payment success page after successful payment
     */
    public function success(Request $request, $paymentId)
    {
        try {
            $payment = Payment::with(['payable', 'currency', 'status', 'payMethod', 'client'])
                ->findOrFail($paymentId);

            Log::info('Payment success page accessed', [
                'payment_id' => $paymentId,
                'user_id' => auth()->id(),
            ]);

            return view('payments.success', [
                'payment' => $payment,
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
            $payment = Payment::with(['payable', 'currency', 'status', 'client'])
                ->findOrFail($paymentId);

            Log::info('Payment pending page accessed', [
                'payment_id' => $paymentId,
                'user_id' => auth()->id(),
            ]);

            return view('payments.pending', [
                'payment' => $payment,
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
            $payment = Payment::with(['payable', 'currency', 'status', 'client'])
                ->findOrFail($paymentId);

            Log::info('Payment failed page accessed', [
                'payment_id' => $paymentId,
                'user_id' => auth()->id(),
            ]);

            return view('payments.failed', [
                'payment' => $payment,
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
