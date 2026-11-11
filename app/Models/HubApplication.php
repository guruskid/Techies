<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HubApplication extends Model
{
    protected $fillable = ['hub_id', 'status', 'review_notes', 'reviewed_by', 'reviewed_at'];

    protected $casts = ['reviewed_at' => 'datetime'];

    public function hub()
    {
        return $this->belongsTo(Hub::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
