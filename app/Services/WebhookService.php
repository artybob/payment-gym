<?php

namespace App\Services;

use App\Repositories\PaymentRepository;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    protected PaymentRepository $paymentRepository;

    public function __construct(PaymentRepository $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    public function handleCallback(array $payload): array
    {
        $payment = $this->paymentRepository->findByExternalId($payload['payment_external_id']);

        if (!$payment) {
            throw new \Exception('Payment not found');
        }

        // Идемпотентность
        if (!in_array($payment->status, ['processing', 'pending'])) {
            Log::warning('Webhook for already processed payment', [
                'payment_id' => $payment->id,
                'current_status' => $payment->status
            ]);
            return ['status' => 'already_processed', 'message' => 'Already processed'];
        }

        $newStatus = $payload['gateway_status'] === 'success' ? 'paid' : 'failed';

        $this->paymentRepository->updateStatus($payment, $newStatus, [
            'metadata->gateway_transaction_id' => $payload['gateway_transaction_id'] ?? null
        ]);

        Log::info('Webhook processed', ['payment_id' => $payment->id, 'new_status' => $newStatus]);

        return ['status' => 'ok'];
    }
}
