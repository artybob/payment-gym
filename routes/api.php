<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WebhookController;

Route::post('/payments', [PaymentController::class, 'create']);
Route::post('/webhook/gateway', [WebhookController::class, 'handleCallback']);
