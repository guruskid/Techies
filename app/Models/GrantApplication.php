<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrantApplication extends Model
{
    protected $fillable = ['grant_id', 'user_id', 'project_title', 'summary', 'budget_breakdown', 'status', 'review_notes', 'reviewed_by', 'reviewed_at'];

    protected $casts = ['reviewed_at' => 'datetime'];

    public function grant()
    {
        return $this->belongsTo(Grant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
