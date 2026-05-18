<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WebhookController;

Route::middleware('auth:api')->get('/user-profile', function (Request $request) {
    return response()->json($request->user());
});

Route::post('/payments', [PaymentController::class, 'create']);
Route::post('/webhook/gateway', [WebhookController::class, 'handleCallback']);
