<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = ['title', 'slug', 'description', 'cover_image', 'category_id', 'organizer_id', 'host', 'key_speakers', 'speaker_images', 'agenda', 'requirements', 'contact_email', 'contact_phone', 'hub_id', 'chapter_id', 'start_date', 'end_date', 'start_time', 'end_time', 'venue', 'city', 'state', 'country', 'is_online', 'meeting_url', 'registration_deadline', 'max_attendees', 'registration_type', 'fee', 'payment_url', 'payment_instructions', 'status'];

    protected $casts = ['speaker_images' => 'array', 'is_online' => 'boolean', 'start_date' => 'date', 'end_date' => 'date', 'registration_deadline' => 'date'];

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

    /**
     * The meeting link is only for people who have a place at the event,
     * the organizer, or staff who manage the event.
     */
    public function requiresPayment(): bool
    {
        return $this->registration_type === 'paid';
    }

    public function canAccessMeetingLink(?User $user): bool
    {
        if (! $this->is_online || blank($this->meeting_url) || ! $user) {
            return false;
        }

        if ($user->isAdmin() || $user->isSuperAdmin() || $this->organizer_id === $user->id) {
            return true;
        }

        return $this->registrations()
            ->where('user_id', $user->id)
            ->where('status', EventRegistration::STATUS_REGISTERED)
            ->exists();
    }
}
