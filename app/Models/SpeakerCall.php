<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpeakerCall extends Model
{
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'title', 'slug', 'description', 'topics', 'event_id', 'hub_id',
        'chapter_id', 'created_by', 'opens_at', 'closes_at', 'status',
    ];

    protected $casts = [
        'topics' => 'array',
        'opens_at' => 'date',
        'closes_at' => 'date',
    ];

    /**
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_OPEN => 'Open for submissions',
            self::STATUS_CLOSED => 'Closed',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function hub()
    {
        return $this->belongsTo(Hub::class);
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function submissions()
    {
        return $this->hasMany(SpeakerSubmission::class);
    }

    /**
     * A call accepts proposals only while it is open and inside its date window.
     */
    public function isOpen(): bool
    {
        if ($this->status !== self::STATUS_OPEN) {
            return false;
        }

        $today = now()->startOfDay();

        if ($this->opens_at && $this->opens_at->startOfDay()->greaterThan($today)) {
            return false;
        }

        if ($this->closes_at && $this->closes_at->endOfDay()->lessThan($today)) {
            return false;
        }

        return true;
    }

    /**
     * @return array<int, string>
     */
    public function topicList(): array
    {
        return collect($this->topics ?? [])
            ->map(fn ($topic) => trim((string) $topic))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
