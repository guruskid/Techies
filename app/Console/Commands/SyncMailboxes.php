<?php

namespace App\Console\Commands;

use App\Models\EmailAccount;
use App\Services\Mail\MailboxSynchronizer;
use Illuminate\Console\Command;
use Throwable;

class SyncMailboxes extends Command
{
    protected $signature = 'mail:sync {--account= : Sync only one account id}';

    protected $description = 'Fetch new messages from connected IMAP inboxes';

    public function handle(MailboxSynchronizer $synchronizer): int
    {
        $accounts = EmailAccount::query()
            ->where('is_active', true)
            ->whereNotNull('imap_host')
            ->whereNotNull('imap_username')
            ->when($this->option('account'), fn ($query, $id) => $query->whereKey($id))
            ->get();

        if ($accounts->isEmpty()) {
            $this->info('No IMAP accounts are configured.');

            return self::SUCCESS;
        }

        $failed = 0;

        foreach ($accounts as $account) {
            try {
                $count = $synchronizer->sync($account);
                $this->info($account->email.': '.$count.' new message(s).');
            } catch (Throwable $exception) {
                $failed++;
                $this->error($account->email.': '.$exception->getMessage());
            }
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
