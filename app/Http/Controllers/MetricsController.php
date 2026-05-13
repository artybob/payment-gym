<?php

namespace App\Http\Controllers;

use App\Services\MetricsService;

class MetricsController extends Controller
{
    protected MetricsService $metricsService;

    public function __construct(MetricsService $metricsService)
    {
        $this->metricsService = $metricsService;
    }

    public function index()
    {
        $metrics = $this->metricsService->getMetrics();

        return response(implode("\n", $metrics), 200)
            ->header('Content-Type', 'text/plain');
    }
}
