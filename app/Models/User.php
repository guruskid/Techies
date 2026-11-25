<?php

namespace App\Models;

use App\Support\Username;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, HasRoles, Notifiable {
        HasRoles::hasRole as traitHasRole;
    }

    protected $fillable = [
        'name', 'email', 'password', 'role', 'status', 'membership_status',
        'membership_reference', 'membership_paid_at', 'membership_confirmed_by',
        'membership_notes', 'member_number', 'referral_code', 'referred_by',
        'is_founding_member', 'feedback_requested_at', 'opportunity_match_sent_at', 'birthday_wished_at',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'membership_paid_at' => 'datetime',
            'feedback_requested_at' => 'datetime',
            'opportunity_match_sent_at' => 'datetime',
            'birthday_wished_at' => 'datetime',
            'is_founding_member' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    protected static function booted(): void
    {
        static::created(function (User $user): void {
            $year = $user->created_at?->format('Y') ?? now()->format('Y');

            $user->forceFill([
                'member_number' => $user->member_number ?: 'MWTT-'.$year.'-'.str_pad((string) $user->id, 5, '0', STR_PAD_LEFT),
                'referral_code' => $user->referral_code ?: Str::upper(Str::random(8)),
            ])->saveQuietly();
        });
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function mentorshipRequest()
    {
        return $this->hasOne(MentorshipRequest::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function feedback()
    {
        return $this->hasMany(MemberFeedback::class);
    }

    public function showcaseProjects()
    {
        return $this->hasMany(ShowcaseProject::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function introRequests()
    {
        return $this->hasMany(IntroRequest::class, 'requester_id');
    }

    public function referralOffers()
    {
        return $this->hasMany(ReferralOffer::class);
    }

    public function loungePosts()
    {
        return $this->hasMany(LoungePost::class);
    }

    public function grantApplications()
    {
        return $this->hasMany(GrantApplication::class);
    }

    public function benefitRedemptions()
    {
        return $this->hasMany(BenefitRedemption::class);
    }

    public function connectedAccounts()
    {
        return $this->hasMany(ConnectedAccount::class);
    }

    /**
     * Staff always have access. Members need a paid or waived membership.
     */
    public function hasActiveMembership(): bool
    {
        if ($this->isAdmin() || $this->isSuperAdmin()) {
            return true;
        }

        return in_array($this->membership_status, ['active', 'exempt'], true);
    }

    public function needsMembershipPayment(): bool
    {
        return ! $this->hasActiveMembership();
    }

    public function membershipIsPending(): bool
    {
        return $this->membership_status === 'pending';
    }

    /**
     * Return this user's member profile, creating one if it is missing.
     *
     * Admins and super admins are community members too, so every account
     * can own a public profile.
     */
    public function coordinatesChapter(): bool
    {
        return Chapter::where('coordinator_id', $this->id)
            ->orWhere('second_coordinator_id', $this->id)
            ->exists();
    }

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
