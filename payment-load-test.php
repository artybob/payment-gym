<?php
// Тест создания платежей с разными суммами
$url = 'http://localhost:8080/api/payments';
$total = 1000; // количество платежей
$concurrent = 50; // параллельных запросов

echo "=== PAYMENT CREATION LOAD TEST ===\n";
echo "Total payments: $total\n";
echo "Concurrent: $concurrent\n";
echo str_repeat("-", 50) . "\n";

$start = microtime(true);
$success = 0;
$failed = 0;
$amounts = [];

// Функция для отправки запроса
function sendPayment($id) {
    global $url;
    $amount = rand(10, 10000) / 100; // случайная сумма от 0.10 до 100.00
    $payload = json_encode([
        'merchant_id' => 'load_test_' . rand(1, 10),
        'amount' => $amount,
        'currency' => 'USD',
        'idempotency_key' => uniqid() . '_' . $id
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['success' => ($httpCode == 202 || $httpCode == 200), 'amount' => $amount, 'code' => $httpCode];
}

// Запускаем параллельные запросы
$multiHandle = curl_multi_init();
$handles = [];

for ($i = 0; $i < $concurrent; $i++) {
    $handles[$i] = curl_init();
    $amount = rand(10, 10000) / 100;
    $payload = json_encode([
        'merchant_id' => 'load_test_' . rand(1, 10),
        'amount' => $amount,
        'currency' => 'USD',
        'idempotency_key' => uniqid() . '_' . $i
    ]);
    curl_setopt($handles[$i], CURLOPT_URL, $url);
    curl_setopt($handles[$i], CURLOPT_POST, true);
    curl_setopt($handles[$i], CURLOPT_POSTFIELDS, $payload);
    curl_setopt($handles[$i], CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($handles[$i], CURLOPT_RETURNTRANSFER, true);
    curl_multi_add_handle($multiHandle, $handles[$i]);
}

$running = null;
do {
    curl_multi_exec($multiHandle, $running);
} while ($running);

foreach ($handles as $handle) {
    $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    if ($httpCode == 202 || $httpCode == 200) {
        $success++;
    } else {
        $failed++;
    }
    curl_multi_remove_handle($multiHandle, $handle);
    curl_close($handle);
}

curl_multi_close($multiHandle);

$end = microtime(true);
$time = $end - $start;

echo "\nRESULTS:\n";
echo "Successful payments: $success\n";
echo "Failed payments: $failed\n";
echo "Success rate: " . round(($success / $total) * 100, 2) . "%\n";
echo "Total time: " . round($time, 2) . " seconds\n";
echo "Payments per second: " . round($success / $time, 2) . "\n";
