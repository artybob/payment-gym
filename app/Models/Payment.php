<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Payment extends Model
{
    use HasUuids;

    protected $fillable = [
        'external_id', 'merchant_id', 'amount', 'currency',
        'status', 'payment_method', 'metadata', 'processed_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];
}
