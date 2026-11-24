<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberSpotlight extends Model
{
    protected $fillable = ['user_id', 'month', 'quote', 'achievement', 'is_published', 'created_by'];

    protected $casts = ['is_published' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
