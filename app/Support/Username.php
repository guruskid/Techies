<?php

namespace App\Support;

use App\Models\UserProfile;
use Closure;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class Username
{
    /**
     * Normalise a username for storage and comparison.
     */
    public static function normalize(?string $username): string
    {
        return strtolower(trim((string) $username));
    }

    /**
     * Validation rules for a public profile username/subdomain.
     *
     * @return array<int, mixed>
     */
    public static function rules(?int $ignoreId = null): array
    {
        return [
            'nullable',
            'string',
            'min:3',
            'max:30',
            'regex:/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/',
            Rule::unique('user_profiles', 'username')->ignore($ignoreId),
            function (string $attribute, mixed $value, Closure $fail): void {
                if (in_array($value, config('techies.reserved_subdomains', []), true)) {
                    $fail('That username is reserved. Please choose another one.');
                }
            },
        ];
    }

    /**
     * Live availability details for a requested username.
     *
     * @return array{available: bool, message: string}
     */
    public static function availability(?string $username, ?int $ignoreId = null): array
    {
        $username = static::normalize($username);

        if ($username === '') {
            return ['available' => false, 'message' => 'Enter a username to check availability.'];
        }

        if (strlen($username) < 3) {
            return ['available' => false, 'message' => 'Usernames must be at least 3 characters.'];
        }

        if (strlen($username) > 30) {
            return ['available' => false, 'message' => 'Usernames must be 30 characters or fewer.'];
        }

        if (! preg_match('/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/', $username)) {
            return ['available' => false, 'message' => 'Use lowercase letters, numbers and hyphens only. Start and end with a letter or number.'];
        }

        if (in_array($username, config('techies.reserved_subdomains', []), true)) {
            return ['available' => false, 'message' => 'That username is reserved. Please choose another one.'];
        }

        $taken = UserProfile::where('username', $username)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();

        if ($taken) {
            return ['available' => false, 'message' => 'That username is already taken.'];
        }

        return [
            'available' => true,
            'message' => 'Available — https://'.$username.'.'.config('techies.profile_domain'),
        ];
    }

    /**
     * Build a unique, subdomain-safe username from a display name.
     */
    public static function generate(string $base): string
    {
        $reserved = config('techies.reserved_subdomains', []);
        $base = trim(Str::slug($base), '-') ?: 'member';

        if (strlen($base) < 3) {
            $base .= '-'.Str::lower(Str::random(4));
        }

        $base = substr($base, 0, 30);
        $candidate = $base;
        $suffix = 1;

        while (
            in_array($candidate, $reserved, true)
            || UserProfile::where('username', $candidate)->exists()
        ) {
            $suffixText = '-'.$suffix;
            $candidate = rtrim(substr($base, 0, 30 - strlen($suffixText)), '-').$suffixText;
            $suffix++;
        }

        return $candidate;
    }
}
