<?php

namespace App\Services;

use App\Repositories\MetricsRepository;

class MetricsService
{
    protected MetricsRepository $metricsRepository;

    public function __construct(MetricsRepository $metricsRepository)
    {
        $this->metricsRepository = $metricsRepository;
    }

    public function getMetrics(): array
    {
        $metrics = [];

        // Статистика по статусам
        $statusStats = $this->metricsRepository->getPaymentsGroupedByStatus();
        foreach ($statusStats as $stat) {
            $metrics[] = "payments_by_status{status=\"{$stat->status}\"} {$stat->count}";
        }

        // Общее количество
        $total = $this->metricsRepository->getTotalPaymentsCount();
        $metrics[] = "payments_total {$total}";

        // Размер очереди
        $queueSize = $this->metricsRepository->getQueueSize();
        $metrics[] = "queue_size {$queueSize}";

        // Статус воркера
        $metrics[] = "worker_up 1";

        return $metrics;
    }
}
