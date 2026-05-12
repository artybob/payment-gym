<?php

namespace App\Jobs;

use App\Models\Payment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(public Payment $payment) {}

    public function handle(): void
    {
        try {
            // Проверяем статус (идемпотентность)
            if (!in_array($this->payment->status, ['pending', 'processing'])) {
                Log::info('Payment already processed', ['payment_id' => $this->payment->id]);
                return;
            }

            $this->payment->update(['status' => 'processing']);

            // Имитация обработки платежа
            sleep(3);

            // Успешная обработка
            $this->payment->update([
                'status' => 'paid',
                'processed_at' => now(),
                'metadata->gateway_response' => 'success'
            ]);

            Log::info('Payment processed successfully', [
                'payment_id' => $this->payment->id,
                'amount' => $this->payment->amount
            ]);
        } catch (Throwable $e) {
            $this->payment->update(['status' => 'failed']);
            Log::error('Payment processing failed', [
                'payment_id' => $this->payment->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}

    // Добавь use в начало файла
    use App\Services\ClickHouseService;

    // В методе handle() после успешной обработки добавь:
    public function handle(): void
    {
        // ... существующий код ...
        
        // Отправляем в ClickHouse для аналитики
        try {
            $clickhouse = new ClickHouseService();
            $clickhouse->insertPayment([
                'merchant_id' => $this->payment->merchant_id,
                'amount' => $this->payment->amount,
                'currency' => $this->payment->currency,
                'status' => $this->payment->status,
                'payment_id' => $this->payment->id,
            ]);
            Log::info('Payment sent to ClickHouse', ['payment_id' => $this->payment->id]);
        } catch (\Exception $e) {
            Log::error('Failed to send payment to ClickHouse', ['error' => $e->getMessage()]);
        }
    }
