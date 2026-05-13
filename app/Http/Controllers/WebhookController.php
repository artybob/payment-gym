<?php

namespace App\Http\Controllers;

use App\Services\WebhookService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    protected WebhookService $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    public function handleCallback(Request $request)
    {
        $payload = $request->validate([
            'payment_external_id' => 'required|string',
            'gateway_status' => 'required|in:success,failed',
            'gateway_transaction_id' => 'nullable|string'
        ]);

        try {
            $result = $this->webhookService->handleCallback($payload);
            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
