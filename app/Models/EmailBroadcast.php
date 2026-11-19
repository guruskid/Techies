<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailBroadcast extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_SENDING = 'sending';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'email_account_id', 'email_template_id', 'created_by',
        'name', 'subject', 'body', 'audience', 'audience_id',
        'status', 'recipients_count', 'sent_count', 'failed_count', 'sent_at', 'scheduled_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'scheduled_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(EmailAccount::class, 'email_account_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(EmailBroadcastRecipient::class);
    }

    /**
     * Human labels for the supported broadcast audiences.
     *
     * @return array<string, string>
     */
    public static function audiences(): array
    {
        return [
            'all_members' => 'All active members',
            'chapter' => 'Members of a chapter',
            'event' => 'Attendees of an event',
            'newsletter' => 'Newsletter subscribers',
            'hub_owners' => 'Hub owners',
            'opportunity_applicants' => 'Applicants for an opportunity',
        ];
    }

    public function audienceLabel(): string
    {
        return static::audiences()[$this->audience] ?? ucfirst(str_replace('_', ' ', $this->audience));
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }
}
