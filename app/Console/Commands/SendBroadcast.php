<?php

namespace App\Console\Commands;

use App\Models\EmailBroadcast;
use App\Services\Mail\BroadcastSender;
use Illuminate\Console\Command;
use Throwable;

class SendBroadcast extends Command
{
    protected $signature = 'mail:broadcast {broadcast : The broadcast id}';

    protected $description = 'Send a prepared email broadcast from the command line';

    public function handle(BroadcastSender $sender): int
    {
        $broadcast = EmailBroadcast::find($this->argument('broadcast'));

        if (! $broadcast) {
            $this->error('No broadcast exists with that id.');

            return self::FAILURE;
        }

        if (in_array($broadcast->status, [EmailBroadcast::STATUS_SENDING, EmailBroadcast::STATUS_SENT], true)) {
            $this->error('That broadcast has already been sent.');

            return self::FAILURE;
        }

        $broadcast->recipients()
            ->where('status', 'failed')
            ->update(['status' => 'pending', 'error' => null]);

        try {
            $sender->send($broadcast);
        } catch (Throwable $exception) {
            $broadcast->update(['status' => EmailBroadcast::STATUS_FAILED]);
            $this->error('Broadcast failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $broadcast->refresh();

        $this->info("{$broadcast->name}: {$broadcast->sent_count} delivered, {$broadcast->failed_count} failed.");

        return $broadcast->failed_count > 0 && $broadcast->sent_count === 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
