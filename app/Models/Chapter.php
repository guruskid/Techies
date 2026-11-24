<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Chapter extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'location', 'country', 'state', 'city', 'description', 'logo', 'coordinator_id', 'second_coordinator_id', 'status'];

    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    public function secondCoordinator()
    {
        return $this->belongsTo(User::class, 'second_coordinator_id');
    }

    /** @return Collection<int, User> */
    public function coordinators(): Collection
    {
        return collect([$this->coordinator, $this->secondCoordinator])->filter();
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
