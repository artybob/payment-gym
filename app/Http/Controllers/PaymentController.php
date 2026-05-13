<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'merchant_id' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'idempotency_key' => 'nullable|string|max:255'
        ]);

        $paymentData = [
            'merchant_id' => $validated['merchant_id'],
            'amount' => $validated['amount'],
            'currency' => $validated['currency'],
            'external_id' => $validated['idempotency_key'] ?? Str::uuid()->toString(),
        ];

        $payment = $this->paymentService->createPayment($paymentData);

        return response()->json($payment, 202);
    }
}
