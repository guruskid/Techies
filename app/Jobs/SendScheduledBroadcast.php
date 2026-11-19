<?php

namespace App\Jobs;

use App\Models\EmailBroadcast;
use App\Services\Mail\BroadcastSender;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

class SendScheduledBroadcast implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    public function __construct(public int $broadcastId) {}

    public function handle(BroadcastSender $sender): void
    {
        $broadcast = EmailBroadcast::find($this->broadcastId);

        if (! $broadcast || $broadcast->status !== EmailBroadcast::STATUS_SCHEDULED) {
            return;
        }

        if ($broadcast->scheduled_at && $broadcast->scheduled_at->isFuture()) {
            $this->release($broadcast->scheduled_at);

            return;
        }

        $broadcast->recipients()
            ->where('status', 'failed')
            ->update(['status' => 'pending', 'error' => null]);

        try {
            $sender->send($broadcast);
        } catch (Throwable $exception) {
            $broadcast->update(['status' => EmailBroadcast::STATUS_FAILED]);

            throw $exception;
        }
    }
}
