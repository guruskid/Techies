<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    /**
     * Record a moderation or administrative action for the audit trail.
     *
     * @param  array<string, mixed>  $changes
     */
    public static function log(string $action, ?Model $model = null, array $changes = []): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => $model ? $model->getMorphClass() : null,
            'model_id' => $model?->getKey(),
            'changes' => $changes ?: null,
            'ip_address' => request()?->ip(),
        ]);
    }
}
