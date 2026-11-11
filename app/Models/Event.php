<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = ['title', 'slug', 'description', 'cover_image', 'category_id', 'organizer_id', 'hub_id', 'chapter_id', 'start_date', 'end_date', 'start_time', 'end_time', 'venue', 'city', 'state', 'country', 'is_online', 'meeting_url', 'registration_deadline', 'max_attendees', 'registration_type', 'fee', 'status'];

    protected $casts = ['is_online' => 'boolean', 'start_date' => 'date', 'end_date' => 'date', 'registration_deadline' => 'date'];

    public function category()
    {
        return $this->belongsTo(EventCategory::class, 'category_id');
    }

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function hub()
    {
        return $this->belongsTo(Hub::class);
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }
}
