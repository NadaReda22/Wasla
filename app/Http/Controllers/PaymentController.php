<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Support\Integrations;

class PaymentController extends Controller
{
    /**
     * Where the guest lands after the Kashier page.
     *
     * DISPLAY ONLY. Nothing here changes payment state, because the URL
     * parameters can be forged. The webhook is the source of truth.
     */
    public function callback(Request $request)
    {
        $kashierOrderId = $request->query('merchantOrderId');

        if (!$kashierOrderId) {
            return redirect()->route('home')->with('error', 'Invalid payment response from gateway.');
        }

        $payment = Payment::where('kashier_order_id', $kashierOrderId)->firstOrFail();
        $booking = $payment->booking;

        return match ($payment->status) {
            'paid' => $this->render('components.booking.success', $booking, 'Deposit paid successfully!'),
            'failed' => $this->render('components.booking.failed', $booking, 'Payment was not completed. Please try again.'),
            // Webhook has not arrived yet -> "confirming your payment" page that refreshes itself
            default => view('components.booking.pending', compact('booking')),
        };
    }

    private function render(string $view, $booking, string $message)
    {
        if (view()->exists($view)) {
            return view($view, compact('booking'))->with('message', $message);
        }

        $type = $view === 'components.booking.success' ? 'success' : 'error';

        return redirect()->route('home')->with($type, $message . ' Reference: ' . $booking->booking_reference);
    }

    /**
     * Secure server-to-server webhook. This is the source of truth.
     */
   public function webhook(Request $request)
    {
        if ($request->input('data.isTestWebhook')) {
            return response()->json(['status' => 'success', 'message' => 'Test webhook received!']);
        }

        if (!$this->verifyKashierSignature($request)) {
            Log::warning('Invalid Kashier Webhook Signature', $request->all());
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $data = $request->input('data', []);
        $reference = $data['merchantOrderId'] ?? null;
        $status = strtoupper((string) ($data['status'] ?? ''));
        $paidAmount = (float) ($data['amount'] ?? 0);
        $paidCurrency = $data['currency'] ?? null;

        if (!$reference) {
            return response()->json(['error' => 'Missing reference'], 400);
        }

        $result = DB::transaction(function () use ($reference, $status, $paidAmount, $paidCurrency) {
            $payment = Payment::where('kashier_order_id', $reference)->lockForUpdate()->first();

            if (!$payment || !$payment->booking) {
                return 'not_found';
            }

            if ($payment->status === 'paid') {
                return 'duplicate';
            }

            $booking = $payment->booking;

            if ($status === 'SUCCESS') {
                // FIXED: Added the minus sign that was missing here
                $amountMismatch = abs($paidAmount - (float) $payment->amount) > 0.01;
                $currencyMismatch = $paidCurrency && strtoupper($paidCurrency) !== strtoupper($payment->currency);

                if ($amountMismatch || $currencyMismatch) {
                    Log::warning('Payment mismatch detected on Webhook - review manually', [
                        'expected' => $payment->amount,
                        'expected_currency' => $payment->currency,
                        'received' => $paidAmount,
                        'received_currency' => $paidCurrency,
                        'order' => $reference,
                    ]);
                    $payment->update(['status' => 'failed']);
                    $booking->update(['payment_status' => 'failed']);

                    return 'mismatch';
                }

                $payment->update([
                    'status' => 'paid',
                ]);

                $booking->update([
                    'payment_status' => 'paid',
                    'deposit_paid' => $payment->amount,
                ]);

                return 'paid';
            }

            if (in_array($status, ['FAILED', 'DECLINED', 'CANCELED', 'VOIDED'])) {
                $payment->update(['status' => 'failed']);
                $booking->update(['payment_status' => 'failed']);

                return 'failed';
            }

            return 'ignored';
        });

        if ($result === 'not_found') {
            return response()->json(['error' => 'Booking/Payment not found'], 404);
        }

        return response()->json(['message' => 'Webhook processed successfully']);
    }

    private function verifyKashierSignature(Request $request): bool
    {
        $signature = $request->header('x-kashier-signature');
        $data = $request->input('data');

        if (!$signature || !is_array($data) || empty($data['signatureKeys'])) {
            return false;
        }

        $keys = $data['signatureKeys'];
        sort($keys, SORT_STRING);

        $parts = [];
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) continue;

            $value = $data[$key];
            if ($value === null) { $parts[] = rawurlencode($key); continue; }
            if (is_bool($value)) $value = $value ? 'true' : 'false';

            $parts[] = rawurlencode($key) . '=' . rawurlencode((string) $value);
        }

        // Fetch dynamic keys via Integrations class
        $kashier = Integrations::kashier();
        
        $expected = hash_hmac('sha256', implode('&', $parts), $kashier['api_key']);

        return hash_equals($expected, (string) $signature);
    }
}