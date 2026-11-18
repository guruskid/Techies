<?php

namespace App\Models;

use App\Support\Username;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, HasRoles, Notifiable {
        HasRoles::hasRole as traitHasRole;
        HasRoles::hasAnyRole as traitHasAnyRole;
    }

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

    /**
     * Return this user's member profile, creating one if it is missing.
     *
     * Admins and super admins are community members too, so every account
     * can own a public profile.
     */
    public function ensureProfile(): UserProfile
    {
        $profile = $this->profile()->firstOrCreate(
            ['user_id' => $this->id],
            [
                'username' => Username::generate($this->name ?: 'member'),
                'is_public' => false,
            ]
        );

        $this->setRelation('profile', $profile);

        return $profile;
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

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function reviewedArticles()
    {
        return $this->hasMany(Article::class, 'reviewed_by');
    }

    public function opportunityApplications()
    {
        return $this->hasMany(OpportunityApplication::class);
    }

    /**
     * Active staff accounts that can review submitted content.
     */
    public static function reviewers(bool $superAdminOnly = false)
    {
        $roles = $superAdminOnly ? ['super_admin'] : ['admin', 'super_admin'];

        return static::query()
            ->where('status', 'active')
            ->where(function ($query) use ($roles): void {
                $query->whereIn('role', $roles)
                    ->orWhereHas('roles', fn ($roleQuery) => $roleQuery->whereIn('name', $roles));
            });
    }

    public function hasRole($roles, ?string $guard = null): bool
    {
        if ($this->traitHasRole($roles, $guard)) {
            return true;
        }

        $current = (string) $this->role;

        if (is_string($roles)) {
            return $current === $roles;
        }

        if (is_array($roles)) {
            return in_array($current, $roles, true);
        }

        return false;
    }

    public function hasAnyRole(...$roles): bool
    {
        if ($this->traitHasAnyRole(...$roles)) {
            return true;
        }

        $current = (string) $this->role;

        foreach ($roles as $role) {
            if (is_array($role) && in_array($current, $role, true)) {
                return true;
            }
            if (is_string($role) && $current === $role) {
                return true;
            }
        }

        return false;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin' || $this->hasRole('super_admin');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->hasRole('admin');
    }
}
