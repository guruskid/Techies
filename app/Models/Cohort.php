<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cohort extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = ['title', 'slug', 'description', 'track', 'starts_at', 'ends_at', 'capacity', 'status', 'created_by'];

    protected $casts = ['starts_at' => 'date', 'ends_at' => 'date'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'cohort_members')->withPivot('status', 'joined_at')->withTimestamps();
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }
}
