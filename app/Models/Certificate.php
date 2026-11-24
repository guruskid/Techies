<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    public const TYPES = [
        'attendance' => 'Event attendance',
        'speaker' => 'Speaking',
        'volunteer' => 'Volunteering',
        'mentorship' => 'Mentorship',
        'chapter' => 'Chapter participation',
    ];

    protected $fillable = [
        'user_id', 'event_id', 'issued_by', 'type', 'title', 'description',
        'certificate_number', 'issued_at',
    ];

    protected $casts = ['issued_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
