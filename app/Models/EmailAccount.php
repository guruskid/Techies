<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EmailAccount extends Model
{
    protected $fillable = [
        'name', 'email', 'from_name', 'reply_to', 'signature',
        'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_encryption',
        'imap_host', 'imap_port', 'imap_username', 'imap_password', 'imap_encryption',
        'inbound_secret', 'requires_signature',
        'is_default', 'is_active', 'created_by',
    ];

    protected $hidden = ['smtp_password', 'imap_password', 'inbound_secret'];

    protected $casts = [
        'smtp_password' => 'encrypted',
        'imap_password' => 'encrypted',
        'inbound_secret' => 'encrypted',
        'requires_signature' => 'boolean',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $account): void {
            $account->inbound_token ??= Str::random(48);
            $account->inbound_secret ??= Str::random(64);
        });

        static::saved(function (self $account): void {
            if ($account->is_default) {
                static::whereKeyNot($account->getKey())->update(['is_default' => false]);
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(EmailMessage::class);
    }

    public function broadcasts(): HasMany
    {
        return $this->hasMany(EmailBroadcast::class);
    }

    public function sharedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'email_account_user')
            ->withPivot('can_manage')
            ->withTimestamps();
    }

    /**
     * Limit mailboxes to the ones a user may open. Super admins see every
     * mailbox; everyone else sees mailboxes they created or were invited to.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($user): void {
            $builder->where('created_by', $user->id)
                ->orWhereHas('sharedUsers', fn (Builder $shared) => $shared->whereKey($user->id));
        });
    }

    public function canBeAccessedBy(User $user): bool
    {
        if ($user->isSuperAdmin() || $this->created_by === $user->id) {
            return true;
        }

        return $this->sharedUsers()->whereKey($user->id)->exists();
    }

    public function canBeManagedBy(User $user): bool
    {
        if ($user->isSuperAdmin() || $this->created_by === $user->id) {
            return true;
        }

        return $this->sharedUsers()
            ->whereKey($user->id)
            ->wherePivot('can_manage', true)
            ->exists();
    }

    /**
     * Accounts that can send mail through SMTP.
     */
    public function scopeSendable(Builder $query): Builder
    {
        return $query->where('is_active', true)->whereNotNull('smtp_host');
    }

    public static function default(): ?self
    {
        return static::where('is_default', true)->where('is_active', true)->first()
            ?? static::where('is_active', true)->orderBy('id')->first();
    }

    public function mailerName(): string
    {
        return 'account_'.$this->getKey();
    }

    /**
     * Configuration for a dynamic Laravel mailer using this account's SMTP details.
     *
     * @return array<string, mixed>
     */
    public function mailerConfig(): array
    {
        return [
            'transport' => 'smtp',
            'scheme' => $this->smtp_encryption === 'ssl' ? 'smtps' : 'smtp',
            'host' => $this->smtp_host,
            'port' => $this->smtp_port ?: 587,
            'username' => $this->smtp_username,
            'password' => $this->smtp_password,
            'timeout' => 20,
            'local_domain' => parse_url((string) config('app.url'), PHP_URL_HOST),
        ];
    }

    public function fromName(): string
    {
        return $this->from_name ?: $this->name;
    }

    public function inboundUrl(): string
    {
        return route('mail.inbound', $this->inbound_token);
    }

    /**
     * Replace the inbound URL token and signing secret after a leak.
     */
    public function rotateInboundCredentials(): void
    {
        $this->forceFill([
            'inbound_token' => Str::random(48),
            'inbound_secret' => Str::random(64),
        ])->save();
    }

    public function canSend(): bool
    {
        return $this->is_active && filled($this->smtp_host) && filled($this->email);
    }

    public function canReceive(): bool
    {
        return $this->is_active && filled($this->imap_host) && filled($this->imap_username);
    }
}
