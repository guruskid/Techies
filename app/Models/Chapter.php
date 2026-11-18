<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chapter extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'location', 'country', 'state', 'description', 'logo', 'coordinator_id', 'status'];

    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'chapter_members');
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
