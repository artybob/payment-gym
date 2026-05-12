<?php
namespace App\Services;

use Illuminate\Support\Facades\Log;

class ClickHouseService
{
    public function insertPayment(array $data): bool
    {
        $sql = "INSERT INTO payment_analytics (merchant_id, amount, currency, status, payment_id, created_at, day_bucket)
                VALUES ('{$data['merchant_id']}', {$data['amount']}, '{$data['currency']}',
                        '{$data['status']}', '{$data['payment_id']}', now(), today())";

        $cmd = sprintf('docker compose exec -T clickhouse clickhouse-client --query "%s" 2>&1', addslashes($sql));
        $output = shell_exec($cmd);

        Log::info('ClickHouse insert result', ['output' => $output, 'cmd' => $cmd]);

        return true;
    }

    public function getMerchantStats(): array
    {
        $sql = "SELECT merchant_id, count() as total_payments, sum(amount) as total_amount
                FROM payment_analytics GROUP BY merchant_id";

        $cmd = sprintf('docker compose exec -T clickhouse clickhouse-client --format TabSeparated --query "%s" 2>/dev/null', addslashes($sql));
        $output = shell_exec($cmd);

        if (!$output) {
            return [];
        }

        return $this->parseResponse($output);
    }

    protected function parseResponse(string $response): array
    {
        $lines = explode("\n", trim($response));
        if (empty($lines)) return [];

        $result = [];
        foreach ($lines as $line) {
            $parts = explode("\t", $line);
            if (count($parts) >= 2) {
                $result[] = [
                    'merchant_id' => $parts[0],
                    'total_payments' => $parts[1] ?? 0,
                    'total_amount' => $parts[2] ?? 0,
                ];
            }
        }
        return $result;
    }
}
