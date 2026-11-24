<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberFeedback extends Model
{
    protected $table = 'member_feedback';

    protected $fillable = ['user_id', 'rating', 'comments', 'submitted_at'];

    protected $casts = ['submitted_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
