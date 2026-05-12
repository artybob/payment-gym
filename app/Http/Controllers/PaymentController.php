<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPaymentJob;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function create(Request $request)
    {
        $validated = $request->validate([
            'merchant_id' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'idempotency_key' => 'nullable|string|max:255'
        ]);

        $idempotencyKey = $validated['idempotency_key'] ?? Str::uuid()->toString();

        // Проверка идемпотентности
        $existing = Payment::where('external_id', $idempotencyKey)->first();
        if ($existing) {
            return response()->json($existing, 200);
        }

        $payment = Payment::create([
            'external_id' => $idempotencyKey,
            'merchant_id' => $validated['merchant_id'],
            'amount' => $validated['amount'],
            'currency' => $validated['currency'],
            'status' => 'pending'
        ]);

        // Отправляем в очередь
        ProcessPaymentJob::dispatch($payment);

        return response()->json($payment, 202);
    }
}
