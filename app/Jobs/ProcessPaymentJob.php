<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Services\PaymentService;
use App\Services\ClickHouseService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(public Payment $payment) {}

    public function handle(PaymentService $paymentService, ClickHouseService $clickhouse): void
    {
        // Обработка платежа
        $paymentService->processPayment($this->payment);

        // Отправляем в ClickHouse (если нужно)
        try {
            $clickhouse->insertPayment([
                'merchant_id' => $this->payment->merchant_id,
                'amount' => $this->payment->amount,
                'currency' => $this->payment->currency,
                'status' => $this->payment->status,
                'payment_id' => $this->payment->id,
            ]);
            Log::info('Payment sent to ClickHouse', ['payment_id' => $this->payment->id]);
        } catch (\Exception $e) {
            Log::error('Failed to send to ClickHouse', ['error' => $e->getMessage()]);
        }
    }
}
