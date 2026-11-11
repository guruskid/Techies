<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = ['title', 'body', 'author_id', 'chapter_id', 'is_published', 'published_at'];

    protected $casts = ['is_published' => 'boolean', 'published_at' => 'datetime'];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}
