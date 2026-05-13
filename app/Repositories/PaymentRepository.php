<?php

namespace App\Repositories;

use App\Models\Payment;
use Illuminate\Support\Str;

class PaymentRepository
{
    public function findById(string $id): ?Payment
    {
        return Payment::find($id);
    }

    public function findByExternalId(string $externalId): ?Payment
    {
        return Payment::where('external_id', $externalId)->first();
    }

    public function create(array $data): Payment
    {
        return Payment::create([
            'external_id' => $data['external_id'] ?? Str::uuid()->toString(),
            'merchant_id' => $data['merchant_id'],
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'status' => $data['status'] ?? 'pending',
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    public function updateStatus(Payment $payment, string $status, array $extra = []): bool
    {
        $updateData = ['status' => $status];

        if ($status === 'paid') {
            $updateData['processed_at'] = now();
        }

        if (!empty($extra)) {
            $updateData = array_merge($updateData, $extra);
        }

        return $payment->update($updateData);
    }

    public function getStats(): array
    {
        return Payment::selectRaw('status, count(*) as count, sum(amount) as total_amount')
            ->groupBy('status')
            ->get()
            ->toArray();
    }

    public function getTotalCount(): int
    {
        return Payment::count();
    }
}
