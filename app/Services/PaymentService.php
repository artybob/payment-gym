<?php

namespace App\Services;

use App\Jobs\ProcessPaymentJob;
use App\Repositories\PaymentRepository;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected PaymentRepository $paymentRepository;

    public function __construct(PaymentRepository $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    public function createPayment(array $data): array
    {
        $existing = $this->paymentRepository->findByExternalId($data['external_id']);

        if ($existing) {
            return $existing->toArray();
        }

        $payment = $this->paymentRepository->create($data);

        // Отправляем в очередь
        ProcessPaymentJob::dispatch($payment);

        Log::info('Payment created', ['payment_id' => $payment->id, 'amount' => $payment->amount]);

        return $payment->toArray();
    }

    public function processPayment($payment): void
    {
        try {
            // Проверяем статус
            if (!in_array($payment->status, ['pending', 'processing'])) {
                Log::info('Payment already processed', ['payment_id' => $payment->id]);
                return;
            }

            $this->paymentRepository->updateStatus($payment, 'processing');

            // Имитация обработки
            sleep(3);

            // Успешная обработка
            $this->paymentRepository->updateStatus($payment, 'paid', [
                'metadata->gateway_response' => 'success'
            ]);

            Log::info('Payment processed successfully', [
                'payment_id' => $payment->id,
                'amount' => $payment->amount
            ]);

        } catch (\Throwable $e) {
            $this->paymentRepository->updateStatus($payment, 'failed');
            Log::error('Payment processing failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
