<?php

namespace App\Http\Controllers;

use App\Models\PaymentSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TabbyCallbackController extends Controller
{
    /**
     * Handle successful payment
     */
    public function success(Request $request)
    {
        $paymentId = $request->query('payment_id');

        Log::info('Tabby payment success callback', [
            'payment_id' => $paymentId,
            'all_params' => $request->all(),
        ]);

        if ($paymentId) {
            $session = PaymentSession::where('payment_id', $paymentId)->first();

            if ($session) {
                $session->update(['status' => 'authorized']);

                return view('tabby.success', [
                    'session' => $session,
                    'caseRecord' => $session->caseRecord,
                ]);
            }
        }

        return view('tabby.success', [
            'session' => null,
            'caseRecord' => null,
        ]);
    }

    /**
     * Handle cancelled payment
     */
    public function cancel(Request $request)
    {
        $paymentId = $request->query('payment_id');

        Log::info('Tabby payment cancel callback', [
            'payment_id' => $paymentId,
            'all_params' => $request->all(),
        ]);

        if ($paymentId) {
            $session = PaymentSession::where('payment_id', $paymentId)->first();

            if ($session) {
                $session->update(['status' => 'cancelled']);

                return view('tabby.cancel', [
                    'session' => $session,
                    'caseRecord' => $session->caseRecord,
                ]);
            }
        }

        return view('tabby.cancel', [
            'session' => null,
            'caseRecord' => null,
        ]);
    }

    /**
     * Handle failed payment
     */
    public function failure(Request $request)
    {
        $paymentId = $request->query('payment_id');

        Log::info('Tabby payment failure callback', [
            'payment_id' => $paymentId,
            'all_params' => $request->all(),
        ]);

        if ($paymentId) {
            $session = PaymentSession::where('payment_id', $paymentId)->first();

            if ($session) {
                $session->update(['status' => 'rejected']);

                return view('tabby.failure', [
                    'session' => $session,
                    'caseRecord' => $session->caseRecord,
                ]);
            }
        }

        return view('tabby.failure', [
            'session' => null,
            'caseRecord' => null,
        ]);
    }
}
