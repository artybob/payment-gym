<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleCallback(Request $request)
    {
        $payload = $request->validate([
            'payment_external_id' => 'required|string',
            'gateway_status' => 'required|in:success,failed',
            'gateway_transaction_id' => 'nullable|string'
        ]);

        $payment = Payment::where('external_id', $payload['payment_external_id'])->firstOrFail();

        // Идемпотентность для вебхука
        if ($payment->status !== 'processing' && $payment->status !== 'pending') {
            Log::warning('Webhook for already processed payment', [
                'payment_id' => $payment->id,
                'current_status' => $payment->status
            ]);
            return response()->json(['message' => 'Already processed'], 200);
        }

        $newStatus = $payload['gateway_status'] === 'success' ? 'paid' : 'failed';
        $payment->update([
            'status' => $newStatus,
            'processed_at' => now(),
            'metadata->gateway_transaction_id' => $payload['gateway_transaction_id'] ?? null
        ]);

        Log::info('Webhook processed', ['payment_id' => $payment->id]);

        return response()->json(['status' => 'ok']);
    }
}
