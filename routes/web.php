<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WebhookController;
use Spatie\Prometheus\Facades\Prometheus;

Route::post('/api/payments', [PaymentController::class, 'create']);
Route::post('/api/webhook/gateway', [WebhookController::class, 'handleCallback']);

Route::get('/metrics', function() {
    return Prometheus::render();
});
Route::get('/metrics', [App\Http\Controllers\MetricsController::class, 'index']);


Route::get('/get-test-token', function () {
    // Берем первого пользователя или создаем тестового
    $user = User::first() ?? User::factory()->create();

    // Генерируем токен (метод доступен благодаря трейту HasApiTokens в User)
    $token = $user->createToken('TestToken')->accessToken;

    return response()->json(['token' => $token]);
});
