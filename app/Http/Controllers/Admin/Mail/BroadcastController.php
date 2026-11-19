<?php

namespace App\Http\Controllers\Admin\Mail;

use App\Http\Controllers\Controller;
use App\Jobs\SendScheduledBroadcast;
use App\Models\Chapter;
use App\Models\EmailAccount;
use App\Models\EmailBroadcast;
use App\Models\EmailBroadcastRecipient;
use App\Models\EmailTemplate;
use App\Models\Event;
use App\Models\NewsletterSubscriber;
use App\Models\Opportunity;
use App\Models\User;
use App\Services\Mail\BroadcastAudience;
use App\Services\Mail\BroadcastSender;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class BroadcastController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $query = EmailBroadcast::with('account', 'creator');

        if (! $user->isSuperAdmin()) {
            $query->where(function ($builder) use ($user): void {
                $builder->where('created_by', $user->id)
                    ->orWhereHas('account', fn ($account) => $account->visibleTo($user));
            });
        }

        return view('admin.mail.broadcasts.index', [
            'broadcasts' => $query->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        $user = auth()->user();

        return view('admin.mail.broadcasts.create', [
            'accounts' => EmailAccount::sendable()
                ->visibleTo($user)
                ->orderByDesc('is_default')
                ->orderBy('name')
                ->get(),
            'defaultAccount' => $this->defaultAccount(),
            'templates' => EmailTemplate::orderBy('name')->get(),
            'chapters' => Chapter::where('status', 'active')->orderBy('name')->get(),
            'events' => Event::orderByDesc('start_date')->limit(100)->get(),
            'opportunities' => Opportunity::orderByDesc('created_at')->limit(100)->get(),
            'counts' => [
                'all_members' => User::where('status', 'active')->count(),
                'newsletter' => NewsletterSubscriber::count(),
                'hub_owners' => User::where('status', 'active')->whereHas('hub')->count(),
            ],
        ]);
    }

    public function store(Request $request, BroadcastAudience $audience): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email_account_id' => ['required', 'exists:email_accounts,id'],
            'email_template_id' => ['nullable', 'exists:email_templates,id'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:50000'],
            'audience' => ['required', Rule::in(array_keys(EmailBroadcast::audiences()))],
            'audience_id' => ['nullable', 'integer'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
        ]);

        $account = EmailAccount::findOrFail($data['email_account_id']);
        abort_unless($account->canBeAccessedBy(auth()->user()), 403, 'You do not have access to that mailbox.');

        $recipients = $audience->recipients($data['audience'], $data['audience_id'] ?? null);

        if ($recipients->isEmpty()) {
            return back()->withInput()->with('error', 'That audience has no recipients yet.');
        }

        $scheduledAt = filled($data['scheduled_at'] ?? null) ? Carbon::parse($data['scheduled_at']) : null;

        $broadcast = EmailBroadcast::create([
            'email_account_id' => $data['email_account_id'],
            'email_template_id' => $data['email_template_id'] ?? null,
            'created_by' => auth()->id(),
            'name' => $data['name'],
            'subject' => $data['subject'],
            'body' => $data['body'],
            'audience' => $data['audience'],
            'audience_id' => $data['audience_id'] ?? null,
            'status' => $scheduledAt ? EmailBroadcast::STATUS_SCHEDULED : EmailBroadcast::STATUS_DRAFT,
            'recipients_count' => $recipients->count(),
            'scheduled_at' => $scheduledAt,
        ]);

        foreach ($recipients->chunk(200) as $chunk) {
            EmailBroadcastRecipient::insert($chunk->map(fn (array $recipient) => [
                'email_broadcast_id' => $broadcast->id,
                'user_id' => $recipient['user_id'],
                'email' => $recipient['email'],
                'name' => $recipient['name'],
                'status' => EmailBroadcastRecipient::STATUS_PENDING,
                'created_at' => now(),
                'updated_at' => now(),
            ])->all());
        }

        if ($scheduledAt) {
            SendScheduledBroadcast::dispatch($broadcast->id)->delay($scheduledAt);
        }

        AuditLogger::log('mail.broadcast.created', $broadcast, [
            'audience' => $broadcast->audience,
            'recipients' => $broadcast->recipients_count,
            'scheduled_at' => $scheduledAt?->toDateTimeString(),
        ]);

        return redirect()
            ->route('admin.mail.broadcasts.show', $broadcast)
            ->with('success', $scheduledAt
                ? 'Broadcast scheduled for '.$scheduledAt->format('M j, Y H:i').' for '.$broadcast->recipients_count.' recipient(s).'
                : 'Broadcast prepared for '.$broadcast->recipients_count.' recipient(s). Review it, then send.');
    }

    public function show(EmailBroadcast $broadcast): View
    {
        $this->authorizeBroadcast($broadcast);

        return view('admin.mail.broadcasts.show', [
            'broadcast' => $broadcast->load('account', 'template', 'creator'),
            'recipients' => $broadcast->recipients()->with('user')->orderBy('email')->paginate(30),
        ]);
    }

    public function send(EmailBroadcast $broadcast, BroadcastSender $sender): RedirectResponse
    {
        $this->authorizeBroadcast($broadcast);

        if ($broadcast->status === EmailBroadcast::STATUS_SENDING || $broadcast->status === EmailBroadcast::STATUS_SENT) {
            return back()->with('error', 'This broadcast has already been sent.');
        }

        $broadcast->recipients()
            ->where('status', EmailBroadcastRecipient::STATUS_FAILED)
            ->update(['status' => EmailBroadcastRecipient::STATUS_PENDING, 'error' => null]);

        @set_time_limit(0);

        try {
            $sender->send($broadcast);
        } catch (Throwable $exception) {
            $broadcast->update(['status' => EmailBroadcast::STATUS_FAILED]);

            return back()->with('error', 'Broadcast failed: '.$exception->getMessage());
        }

        AuditLogger::log('mail.broadcast.sent', $broadcast, [
            'sent' => $broadcast->sent_count,
            'failed' => $broadcast->failed_count,
        ]);

        return back()->with('success', 'Broadcast sent — '.$broadcast->sent_count.' delivered, '.$broadcast->failed_count.' failed.');
    }

    public function destroy(EmailBroadcast $broadcast): RedirectResponse
    {
        $this->authorizeBroadcast($broadcast);

        if ($broadcast->status === EmailBroadcast::STATUS_SENDING) {
            return back()->with('error', 'A broadcast cannot be deleted while it is sending.');
        }

        $name = Str::limit($broadcast->name, 60);
        $broadcast->delete();

        AuditLogger::log('mail.broadcast.deleted', null, ['name' => $name]);

        return redirect()->route('admin.mail.broadcasts.index')->with('success', 'Broadcast deleted.');
    }

    private function authorizeBroadcast(EmailBroadcast $broadcast): void
    {
        $user = auth()->user();

        if ($user->isSuperAdmin() || $broadcast->created_by === $user->id) {
            return;
        }

        abort_unless($broadcast->account && $broadcast->account->canBeAccessedBy($user), 403);
    }

    private function defaultAccount(): ?EmailAccount
    {
        $user = auth()->user();

        return EmailAccount::visibleTo($user)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first()
            ?? EmailAccount::visibleTo($user)
                ->where('is_active', true)
                ->orderBy('id')
                ->first();
    }
}
