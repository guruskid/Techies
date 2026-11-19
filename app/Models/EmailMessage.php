<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailMessage extends Model
{
    public const DIRECTION_OUTBOUND = 'outbound';

    public const DIRECTION_INBOUND = 'inbound';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    public const STATUS_RECEIVED = 'received';

    protected $fillable = [
        'email_account_id', 'email_broadcast_id', 'user_id',
        'direction', 'status', 'from_email', 'from_name', 'to_email', 'to_name',
        'cc', 'bcc', 'subject', 'body_html', 'body_text',
        'message_id', 'in_reply_to', 'error',
        'sent_at', 'received_at', 'read_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'received_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(EmailAccount::class, 'email_account_id');
    }

    public function broadcast(): BelongsTo
    {
        return $this->belongsTo(EmailBroadcast::class, 'email_broadcast_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(EmailAttachment::class);
    }

    public function isInbound(): bool
    {
        return $this->direction === self::DIRECTION_INBOUND;
    }

    public function isUnread(): bool
    {
        return $this->isInbound() && $this->read_at === null;
    }

    public function preview(int $length = 140): string
    {
        $text = $this->body_text ?: strip_tags((string) $this->body_html);

        return str($text)->squish()->limit($length)->toString();
    }
}
