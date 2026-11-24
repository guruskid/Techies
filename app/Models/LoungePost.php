<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoungePost extends Model
{
    protected $fillable = ['user_id', 'title', 'body', 'category', 'is_pinned'];

    protected $casts = ['is_pinned' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replies()
    {
        return $this->hasMany(LoungeReply::class);
    }
}
