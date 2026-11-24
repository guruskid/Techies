<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntroRequest extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_DECLINED = 'declined';

    protected $fillable = ['requester_id', 'target_user_id', 'target_name', 'purpose', 'context', 'status', 'admin_notes', 'handled_by', 'handled_at'];

    protected $casts = ['handled_at' => 'datetime'];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function target()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
