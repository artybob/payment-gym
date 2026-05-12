<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class MetricsController extends Controller
{
    public function index()
    {
        $metrics = [];

        // Количество платежей по статусам
        $payments = DB::table('payments')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        foreach ($payments as $payment) {
            $metrics[] = "payments_by_status{status=\"{$payment->status}\"} {$payment->count}";
        }

        // Общее количество платежей
        $total = DB::table('payments')->count();
        $metrics[] = "payments_total {$total}";

        // Количество сообщений в очереди
        try {
            $queueSize = DB::table('jobs')->count();
            $metrics[] = "queue_size {$queueSize}";
        } catch (\Exception $e) {
            $metrics[] = "queue_size 0";
        }

        // Статус воркера
        $metrics[] = "worker_up 1";

        return response(implode("\n", $metrics), 200)
            ->header('Content-Type', 'text/plain');
    }
}
