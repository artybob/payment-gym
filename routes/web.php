<?php

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

// Scribe documentation
Route::get('/docs', function () {
    return view('scribe.index');
})->name('scribe');

Route::get('/docs/{any}', function () {
    return view('scribe.index');
})->where('any', '.*');
