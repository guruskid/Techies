<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventRegistration extends Model
{
    public const STATUS_REGISTERED = 'registered';

    public const STATUS_WAITLISTED = 'waitlisted';

    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_NOT_REQUIRED = 'not_required';

    public const PAYMENT_UNPAID = 'unpaid';

    public const PAYMENT_PAID = 'paid';

    public const PAYMENT_REFUNDED = 'refunded';

    protected $fillable = [
        'event_id', 'user_id', 'status', 'payment_status', 'payment_reference',
        'paid_at', 'refunded_at', 'waitlisted_at', 'reminded_at', 'checked_in_at',
    ];

    protected $casts = [
        'waitlisted_at' => 'datetime',
        'reminded_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public static function paymentStatuses(): array
    {
        return [
            self::PAYMENT_NOT_REQUIRED => 'Not required',
            self::PAYMENT_UNPAID => 'Unpaid',
            self::PAYMENT_PAID => 'Paid',
            self::PAYMENT_REFUNDED => 'Refunded',
        ];
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
