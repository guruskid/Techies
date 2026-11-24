<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorshipRequest extends Model
{
    public const ROLE_MENTOR = 'mentor';

    public const ROLE_MENTEE = 'mentee';

    public const STATUS_PENDING = 'pending';

    public const STATUS_MATCHED = 'matched';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'user_id', 'role', 'expertise', 'goals', 'availability', 'status',
        'matched_with', 'matched_at', 'notes',
    ];

    protected $casts = ['matched_at' => 'datetime'];

    /**
     * @return array<string, string>
     */
    public static function roles(): array
    {
        return [
            self::ROLE_MENTOR => 'Become a mentor',
            self::ROLE_MENTEE => 'Find a mentor',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function match()
    {
        return $this->belongsTo(User::class, 'matched_with');
    }

    public function isMatched(): bool
    {
        return $this->status === self::STATUS_MATCHED && $this->matched_with !== null;
    }
}
