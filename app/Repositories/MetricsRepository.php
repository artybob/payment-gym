<?php


namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class MetricsRepository
{
    public function getPaymentsGroupedByStatus(): array
    {
        return DB::table('payments')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->toArray();
    }

    public function getTotalPaymentsCount(): int
    {
        return DB::table('payments')->count();
    }

    public function getQueueSize(): int
    {
        try {
            return DB::table('jobs')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
}
