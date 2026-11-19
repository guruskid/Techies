<?php

namespace App\Console\Commands;

use App\Models\EmailBroadcast;
use App\Services\Mail\BroadcastSender;
use Illuminate\Console\Command;
use Throwable;

class SendDueBroadcasts extends Command
{
    protected $signature = 'mail:broadcasts:send-due';

    protected $description = 'Send scheduled broadcasts whose time has arrived';

    public function handle(BroadcastSender $sender): int
    {
        $due = EmailBroadcast::where('status', EmailBroadcast::STATUS_SCHEDULED)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->get();

        if ($due->isEmpty()) {
            $this->info('No scheduled broadcasts are due.');

            return self::SUCCESS;
        }

        foreach ($due as $broadcast) {
            try {
                $sender->send($broadcast);
                $this->info("{$broadcast->name}: {$broadcast->sent_count} delivered, {$broadcast->failed_count} failed.");
            } catch (Throwable $exception) {
                $broadcast->update(['status' => EmailBroadcast::STATUS_FAILED]);
                $this->error("{$broadcast->name} failed: {$exception->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
