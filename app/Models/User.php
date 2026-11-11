<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'status',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function memberProfile()
    {
        return $this->hasOne(MemberProfile::class);
    }

    public function hub()
    {
        return $this->hasOne(Hub::class);
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'member_skills');
    }

    public function interests()
    {
        return $this->belongsToMany(Interest::class, 'member_interests');
    }

    public function chapters()
    {
        return $this->belongsToMany(Chapter::class, 'chapter_members');
    }

    public function workExperiences()
    {
        return $this->hasMany(WorkExperience::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function socialLinks()
    {
        return $this->hasMany(MemberSocialLink::class);
    }

    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }
}
