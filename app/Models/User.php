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
            ->whereIn('role', $roles);
    }

    /**
     * The roles whose authority lives in the users.role column.
     *
     * Spatie roles are still used for functional capabilities such as hub
     * ownership, but staff access always follows users.role so a stale pivot
     * row can never keep someone signed in with more access than they were
     * granted.
     *
     * @var array<int, string>
     */
    private const PRIMARY_ROLES = ['member', 'admin', 'super_admin'];

    public function hasRole($roles, ?string $guard = null): bool
    {
        if (is_string($roles) && str_contains($roles, '|')) {
            $roles = explode('|', $roles);
        }

        $names = is_array($roles) ? $roles : [$roles];
        $primary = array_values(array_filter(
            $names,
            fn ($role) => is_string($role) && in_array($role, self::PRIMARY_ROLES, true),
        ));

        if ($primary !== [] && in_array((string) $this->role, $primary, true)) {
            return true;
        }

        $secondary = array_values(array_filter(
            $names,
            fn ($role) => ! (is_string($role) && in_array($role, self::PRIMARY_ROLES, true)),
        ));

        foreach ($secondary as $role) {
            if ($this->traitHasRole($role, $guard)) {
                return true;
            }
        }

        return false;
    }

    public function hasAnyRole(...$roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Fingerprint of the access a session was granted. Any change to the
     * primary role, status or assigned roles invalidates old sessions.
     */
    public function accessSignature(): string
    {
        return hash('sha256', implode('|', [
            (string) $this->getKey(),
            (string) $this->role,
            (string) $this->status,
            $this->roles()->orderBy('name')->pluck('name')->implode(','),
        ]));
    }
}
