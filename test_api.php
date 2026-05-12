<?php
$url = 'http://localhost:8080/api/payments';
$total = 20; // количество запросов

echo "=== Тестирование API платежей ===\n";
echo "Всего запросов: $total\n";
echo str_repeat("-", 50) . "\n";

$start = microtime(true);
$success = 0;
$failed = 0;

for ($i = 1; $i <= $total; $i++) {
    $data = [
        'merchant_id' => 'merchant_' . rand(1, 5),
        'amount' => rand(10, 1000) / 1,
        'currency' => 'USD'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200 || $httpCode == 201 || $httpCode == 202) {
        $success++;
        echo "✅ Запрос $i: успешно\n";
    } else {
        $failed++;
        echo "❌ Запрос $i: ошибка (HTTP $httpCode)\n";
    }
}

$end = microtime(true);
$time = round($end - $start, 2);

echo str_repeat("-", 50) . "\n";
echo "РЕЗУЛЬТАТЫ:\n";
echo "✅ Успешных: $success\n";
echo "❌ Неудачных: $failed\n";
echo "⏱️  Время выполнения: {$time} сек\n";
echo "📊 RPS: " . round($total / $time, 2) . "\n";
