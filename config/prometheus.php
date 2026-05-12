<?php

return [
    'collectors' => [
        Spatie\Prometheus\Collectors\Http\RequestDurationCollector::class,
        Spatie\Prometheus\Collectors\Http\RequestCountCollector::class,
        Spatie\Prometheus\Collectors\Http\RequestErrorCollector::class,
        Spatie\Prometheus\Collectors\Queue\QueueSizeCollector::class,
        Spatie\Prometheus\Collectors\Queue\QueueJobProcessingTimeCollector::class,
    ],
];
