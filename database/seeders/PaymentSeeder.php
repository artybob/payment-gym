<?php

namespace Database\Seeders;

use App\Models\Payment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['pending', 'processing', 'paid', 'failed', 'refunded'];
        $currencies = ['USD', 'EUR', 'RUB', 'GBP'];
        $paymentMethods = ['visa', 'mastercard', 'mir', 'bank_transfer', 'sbp'];
        $banks = ['sberbank', 'tinkoff', 'alfa_bank', 'vtb', 'gazprombank'];

        $payments = [];

        for ($i = 0; $i < 10; $i++) {
            $status = $statuses[array_rand($statuses)];
            $processedAt = in_array($status, ['paid', 'failed', 'refunded'])
                ? now()->subMinutes(rand(1, 1000))
                : null;

            $bank = $banks[array_rand($banks)];

            $payments[] = [
                'id' => Str::uuid(),
                'external_id' => Str::uuid(),
                'merchant_id' => $bank,
                'amount' => rand(500, 50000) / 100,
                'currency' => $currencies[array_rand($currencies)],
                'status' => $status,
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'metadata' => json_encode([
                    'bank' => $bank,
                    'transaction_type' => ['purchase', 'refund', 'chargeback'][array_rand(['purchase', 'refund', 'chargeback'])],
                    'description' => 'Bank payment #' . ($i + 1),
                ]),
                'processed_at' => $processedAt,
                'created_at' => now()->subMinutes(rand(10, 10000)),
                'updated_at' => now()->subMinutes(rand(1, 500)),
            ];
        }

        Payment::insert($payments);
    }
}
