<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grant extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = ['title', 'slug', 'description', 'amount', 'opens_at', 'closes_at', 'status', 'created_by'];

    protected $casts = ['opens_at' => 'date', 'closes_at' => 'date'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function applications()
    {
        return $this->hasMany(GrantApplication::class);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN && (! $this->closes_at || $this->closes_at->endOfDay()->isFuture());
    }
}
