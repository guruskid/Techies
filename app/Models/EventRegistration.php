<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventRegistration extends Model
{
    public const STATUS_REGISTERED = 'registered';

    public const STATUS_WAITLISTED = 'waitlisted';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = ['event_id', 'user_id', 'status', 'waitlisted_at', 'reminded_at', 'checked_in_at'];

    protected $casts = [
        'waitlisted_at' => 'datetime',
        'reminded_at' => 'datetime',
        'checked_in_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
