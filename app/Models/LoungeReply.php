<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoungeReply extends Model
{
    protected $fillable = ['lounge_post_id', 'user_id', 'body'];

    public function post()
    {
        return $this->belongsTo(LoungePost::class, 'lounge_post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
