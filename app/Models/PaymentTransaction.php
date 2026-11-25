<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'user_id', 'provider', 'reference', 'purpose', 'amount', 'currency', 'status', 'metadata', 'paid_at',
        'refund_reference', 'refunded_at',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'metadata' => 'array', 'paid_at' => 'datetime', 'refunded_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
