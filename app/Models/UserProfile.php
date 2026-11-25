<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id', 'username', 'first_name', 'last_name', 'phone', 'date_of_birth', 'avatar',
        'country', 'state', 'city', 'professional_title', 'technology_category',
        'experience_level', 'current_organization', 'education', 'bio', 'headline',
        'linkedin', 'github', 'facebook', 'twitter', 'tiktok',
        'areas_of_interest', 'preferred_participation', 'membership_type',
        'is_public', 'weekly_digest', 'profile_completion', 'likes', 'certifications', 'languages',
        'availability', 'open_to_work', 'open_to_mentor', 'open_to_collaborate',
        'available_for_speaking', 'hiring',
        'notification_preferences',
    ];

    protected $casts = [
        'areas_of_interest' => 'array', 'is_public' => 'boolean', 'weekly_digest' => 'boolean',
        'date_of_birth' => 'date',
        'open_to_work' => 'boolean', 'open_to_mentor' => 'boolean', 'open_to_collaborate' => 'boolean',
        'available_for_speaking' => 'boolean', 'hiring' => 'boolean',
        'notification_preferences' => 'array',
    ];

    /**
     * Fields that count towards profile completion, keyed by column with a human label.
     *
     * @return array<string, string>
     */
    public static function completionFields(): array
    {
        return [
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'phone' => 'Phone',
            'headline' => 'Headline',
            'professional_title' => 'Professional title',
            'technology_category' => 'Technology category',
            'experience_level' => 'Experience level',
            'current_organization' => 'Current organization',
            'education' => 'Education',
            'bio' => 'Bio',
            'country' => 'Country',
            'state' => 'State',
            'city' => 'City',
            'linkedin' => 'LinkedIn',
            'github' => 'GitHub',
            'facebook' => 'Facebook',
            'twitter' => 'Twitter (X)',
            'tiktok' => 'TikTok',
            'certifications' => 'Certifications',
            'languages' => 'Languages',
            'availability' => 'Availability',
        ];
    }

    public function completionPercentage(): int
    {
        $fields = array_keys(static::completionFields());
        $filled = 0;

        foreach ($fields as $field) {
            if (filled($this->{$field})) {
                $filled++;
            }
        }

        return (int) round(($filled / count($fields)) * 100);
    }

    /**
     * @return array<int, string>
     */
    public function missingCompletionFields(): array
    {
        $missing = [];

        foreach (static::completionFields() as $field => $label) {
            if (blank($this->{$field})) {
                $missing[] = $label;
            }
        }

        return $missing;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The public URL for this profile.
     *
     * Public profiles live on their own subdomain. Private profiles (and
     * profiles that have not picked a username yet) stay on the standard
     * member path so the owner can still preview them.
     */
    public function publicUrl(): string
    {
        $username = $this->username ?: 'user'.$this->user_id;

        if (! $this->is_public) {
            return route('members.show', $username);
        }

        return static::profileSubdomainUrl($username);
    }

    /**
     * Build a public profile URL using the host the visitor is browsing on,
     * so local development works with username.localhost or username.127.0.0.1.
     */
    public static function profileSubdomainUrl(string $username): string
    {
        $canonical = (string) config('techies.profile_domain');
        $base = $canonical;
        $request = app()->runningInConsole() ? null : request();
        $scheme = $request?->getScheme() ?: (parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'https');
        $port = $request?->getPort();

        if ($request) {
            $host = $request->getHost();
            $candidates = array_unique(array_filter(array_merge(
                [$canonical],
                config('techies.profile_extra_domains', [])
            )));

            foreach ($candidates as $candidate) {
                if ($host === $candidate || str_ends_with($host, '.'.$candidate)) {
                    $base = $candidate;
                    break;
                }
            }
        }

        $url = $scheme.'://'.$username.'.'.$base;

        if ($port && ! in_array($port, [80, 443], true)) {
            $url .= ':'.$port;
        }

        return $url;
    }

    public function isReservedUsername(string $username): bool
    {
        return in_array(strtolower($username), config('techies.reserved_subdomains', []), true);
    }
}
