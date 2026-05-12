<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClickHouseService
{
    protected string $host;
    protected int $port;

    public function __construct()
    {
        $this->host = env('CLICKHOUSE_HOST', 'clickhouse');
        $this->port = env('CLICKHOUSE_PORT', 8123);
    }

    public function query(string $sql): array
    {
        try {
            $response = Http::timeout(30)->get("http://{$this->host}:{$this->port}/", [
                'query' => $sql
            ]);

            if ($response->successful()) {
                return $this->parseResponse($response->body(), $sql);
            }

            Log::error('ClickHouse query failed', ['sql' => $sql, 'error' => $response->body()]);
            return [];
        } catch (\Exception $e) {
            Log::error('ClickHouse query error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function execute(string $sql): bool
    {
        try {
            $response = Http::timeout(30)->post("http://{$this->host}:{$this->port}/", [
                'query' => $sql
            ]);

            if (!$response->successful()) {
                Log::error('ClickHouse execute failed', ['sql' => $sql, 'error' => $response->body()]);
            }

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('ClickHouse execute error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function insertPayment(array $data): bool
    {
        $sql = "INSERT INTO payment_analytics (merchant_id, amount, currency, status, payment_id, created_at, day_bucket)
                VALUES ('{$data['merchant_id']}', {$data['amount']}, '{$data['currency']}',
                        '{$data['status']}', '{$data['payment_id']}', now(), today())";

        return $this->execute($sql);
    }

    public function getMerchantStats(): array
    {
        $sql = "SELECT
                    merchant_id,
                    count() as total_payments,
                    sum(amount) as total_amount,
                    avg(amount) as avg_amount,
                    countIf(status='paid') as paid_count
                FROM payment_analytics
                GROUP BY merchant_id
                ORDER BY total_amount DESC
                LIMIT 10";

        $result = $this->query($sql);

        // Преобразуем в именованный массив
        $formatted = [];
        foreach ($result as $row) {
            $formatted[] = [
                'merchant_id' => $row[0] ?? null,
                'total_payments' => (int)($row[1] ?? 0),
                'total_amount' => (float)($row[2] ?? 0),
                'avg_amount' => (float)($row[3] ?? 0),
                'paid_count' => (int)($row[4] ?? 0)
            ];
        }

        return $formatted;
    }

    public function getDailyStats(): array
    {
        $sql = "SELECT
                    day_bucket,
                    count() as payments_count,
                    sum(amount) as daily_total
                FROM payment_analytics
                GROUP BY day_bucket
                ORDER BY day_bucket DESC
                LIMIT 30";

        $result = $this->query($sql);

        $formatted = [];
        foreach ($result as $row) {
            $formatted[] = [
                'day_bucket' => $row[0] ?? null,
                'payments_count' => (int)($row[1] ?? 0),
                'daily_total' => (float)($row[2] ?? 0)
            ];
        }

        return $formatted;
    }

    protected function parseResponse(string $response, string $sql = ''): array
    {
        $lines = explode("\n", trim($response));
        if (empty($lines)) {
            return [];
        }

        // ClickHouse возвращает данные без заголовков, просто строки с табуляцией
        $result = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            $values = explode("\t", $line);
            $result[] = $values;
        }

        Log::info('ClickHouse parsed response', [
            'sql' => substr($sql, 0, 100),
            'rows' => count($result),
            'first_row' => $result[0] ?? null
        ]);

        return $result;
    }
}
