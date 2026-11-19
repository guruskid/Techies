<?php

namespace App\Http\Controllers\Admin\Mail;

use App\Http\Controllers\Controller;
use App\Models\EmailAccount;
use App\Models\EmailAttachment;
use App\Models\EmailBroadcast;
use App\Models\EmailDraft;
use App\Models\EmailMessage;
use App\Models\EmailTemplate;
use App\Services\Mail\MailAccountMailer;
use App\Services\Mail\MailAttachmentStore;
use App\Support\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class MessageController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $messages = EmailMessage::query()->whereHas('account', fn ($query) => $query->visibleTo($user));

        $broadcasts = EmailBroadcast::query();

        if (! $user->isSuperAdmin()) {
            $broadcasts->where(function ($query) use ($user): void {
                $query->where('created_by', $user->id)
                    ->orWhereHas('account', fn ($account) => $account->visibleTo($user));
            });
        }

        return view('admin.mail.index', [
            'accounts' => EmailAccount::visibleTo($user)->orderByDesc('is_default')->orderBy('name')->get(),
            'defaultAccount' => $this->defaultAccount(),
            'unreadCount' => (clone $messages)->where('direction', EmailMessage::DIRECTION_INBOUND)->whereNull('read_at')->count(),
            'sentThisMonth' => (clone $messages)->where('direction', EmailMessage::DIRECTION_OUTBOUND)
                ->where('status', EmailMessage::STATUS_SENT)
                ->where('sent_at', '>=', now()->startOfMonth())
                ->count(),
            'broadcastCount' => $broadcasts->count(),
            'recentInbox' => (clone $messages)->with('account')
                ->where('direction', EmailMessage::DIRECTION_INBOUND)
                ->latest('received_at')
                ->limit(6)
                ->get(),
            'recentSent' => (clone $messages)->with('account')
                ->where('direction', EmailMessage::DIRECTION_OUTBOUND)
                ->latest('sent_at')
                ->limit(6)
                ->get(),
        ]);
    }

    public function inbox(Request $request): View
    {
        $user = auth()->user();

        $query = EmailMessage::with('account')
            ->whereHas('account', fn ($account) => $account->visibleTo($user))
            ->where('direction', EmailMessage::DIRECTION_INBOUND);

        if ($request->filled('account')) {
            $query->where('email_account_id', $request->integer('account'));
        }

        if ($request->boolean('unread')) {
            $query->whereNull('read_at');
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($builder) use ($search): void {
                $builder->where('subject', 'like', "%{$search}%")
                    ->orWhere('from_email', 'like', "%{$search}%")
                    ->orWhere('from_name', 'like', "%{$search}%");
            });
        }

        return view('admin.mail.inbox', [
            'messages' => $query->latest('received_at')->paginate(25)->withQueryString(),
            'accounts' => EmailAccount::visibleTo($user)->orderBy('name')->get(),
            'search' => $request->string('search')->toString(),
            'accountFilter' => $request->integer('account'),
            'unreadOnly' => $request->boolean('unread'),
            'unreadCount' => EmailMessage::whereHas('account', fn ($account) => $account->visibleTo($user))
                ->where('direction', EmailMessage::DIRECTION_INBOUND)
                ->whereNull('read_at')
                ->count(),
        ]);
    }

    public function showMessage(EmailMessage $message): View
    {
        $this->authorizeMessage($message);

        if ($message->isInbound() && $message->read_at === null) {
            $message->update(['read_at' => now()]);
        }

        return view('admin.mail.show', [
            'message' => $message->load('account'),
            'thread' => $this->thread($message),
        ]);
    }

    public function sent(Request $request): View
    {
        $query = EmailMessage::with('account')
            ->whereHas('account', fn ($account) => $account->visibleTo(auth()->user()))
            ->where('direction', EmailMessage::DIRECTION_OUTBOUND);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($builder) use ($search): void {
                $builder->where('subject', 'like', "%{$search}%")
                    ->orWhere('to_email', 'like', "%{$search}%");
            });
        }

        return view('admin.mail.sent', [
            'messages' => $query->latest('created_at')->paginate(25)->withQueryString(),
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function compose(Request $request): View
    {
        $replyTo = null;

        if ($request->filled('reply')) {
            $replyTo = EmailMessage::findOrFail($request->integer('reply'));
            $this->authorizeMessage($replyTo);
        }

        $draft = (! $request->filled('reply') && ! $request->filled('template'))
            ? EmailDraft::where('user_id', auth()->id())->first()
            : null;

        return view('admin.mail.compose', [
            'accounts' => EmailAccount::sendable()
                ->visibleTo(auth()->user())
                ->orderByDesc('is_default')
                ->orderBy('name')
                ->get(),
            'defaultAccount' => $this->defaultAccount(),
            'templates' => EmailTemplate::orderBy('name')->get(),
            'replyTo' => $replyTo,
            'selectedTemplate' => $request->filled('template') ? EmailTemplate::find($request->integer('template')) : null,
            'draft' => $draft,
        ]);
    }

    public function saveDraft(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email_account_id' => ['nullable', 'integer', 'exists:email_accounts,id'],
            'to' => ['nullable', 'string', 'max:2000'],
            'cc' => ['nullable', 'string', 'max:2000'],
            'bcc' => ['nullable', 'string', 'max:2000'],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:20000'],
            'in_reply_to' => ['nullable', 'string', 'max:255'],
        ]);

        if (filled($data['email_account_id'] ?? null)) {
            $account = EmailAccount::find($data['email_account_id']);

            if (! $account || ! $account->canBeAccessedBy(auth()->user())) {
                $data['email_account_id'] = null;
            }
        }

        $draft = EmailDraft::updateOrCreate(
            ['user_id' => auth()->id()],
            [...$data, 'user_id' => auth()->id()],
        );

        return response()->json([
            'status' => 'ok',
            'saved_at' => $draft->updated_at?->toIso8601String(),
        ]);
    }

    public function discardDraft(Request $request): JsonResponse|RedirectResponse
    {
        EmailDraft::where('user_id', auth()->id())->delete();

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back()->with('success', 'Draft discarded.');
    }

    public function send(Request $request, MailAccountMailer $mailer, MailAttachmentStore $attachments): RedirectResponse
    {
        $data = $request->validate([
            'email_account_id' => ['required', 'exists:email_accounts,id'],
            'to' => ['required', 'string', 'max:2000'],
            'cc' => ['nullable', 'string', 'max:2000'],
            'bcc' => ['nullable', 'string', 'max:2000'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:20000'],
            'in_reply_to' => ['nullable', 'string', 'max:255'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240'],
        ]);

        $invalid = collect($mailer->splitAddresses($data['to']))
            ->map(fn (array $address) => $address[0])
            ->reject(fn (string $email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->values();

        if ($invalid->isNotEmpty()) {
            return back()->withInput()->withErrors(['to' => 'Invalid recipient address: '.$invalid->implode(', ')]);
        }

        $account = EmailAccount::findOrFail($data['email_account_id']);

        abort_unless($account->canBeAccessedBy(auth()->user()) && $account->canSend(), 403, 'You do not have access to that mailbox.');

        $message = EmailMessage::create([
            'email_account_id' => $account->id,
            'user_id' => auth()->id(),
            'direction' => EmailMessage::DIRECTION_OUTBOUND,
            'status' => 'draft',
            'from_email' => $account->email,
            'from_name' => $account->fromName(),
            'to_email' => $data['to'],
            'cc' => $data['cc'] ?? null,
            'bcc' => $data['bcc'] ?? null,
            'subject' => $data['subject'],
            'body_html' => $data['body'],
            'in_reply_to' => $data['in_reply_to'] ?? null,
        ]);

        foreach ($request->file('attachments', []) as $file) {
            $attachments->storeUploaded($message, $file);
        }

        try {
            $mailer->send($account, $message);
        } catch (Throwable $exception) {
            $message->update([
                'status' => EmailMessage::STATUS_FAILED,
                'error' => $exception->getMessage(),
            ]);

            return back()->withInput()->with('error', 'The message could not be sent: '.$exception->getMessage());
        }

        EmailDraft::where('user_id', auth()->id())->delete();

        AuditLogger::log('mail.message.sent', $message, ['to' => $message->to_email]);

        return redirect()
            ->route('admin.mail.sent.show', $message)
            ->with('success', 'Message sent to '.$message->to_email.'.');
    }

    public function downloadAttachment(EmailAttachment $attachment)
    {
        $this->authorizeMessage($attachment->message);

        $disk = Storage::disk($attachment->disk);

        abort_unless($disk->exists($attachment->path), 404);

        return $disk->download($attachment->path, $attachment->filename);
    }

    private function authorizeMessage(EmailMessage $message): void
    {
        $user = auth()->user();

        if (! $message->account) {
            abort_unless($user->isSuperAdmin(), 403);

            return;
        }

        abort_unless($message->account->canBeAccessedBy($user), 403);
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

    /**
     * @return Collection<int, EmailMessage>
     */
    private function thread(EmailMessage $message): Collection
    {
        $reference = $message->in_reply_to ?: $message->message_id;

        if (blank($reference)) {
            return collect();
        }

        return EmailMessage::with('account')
            ->whereHas('account', fn ($account) => $account->visibleTo(auth()->user()))
            ->whereKeyNot($message->id)
            ->where(function ($query) use ($message): void {
                $query->where('message_id', $message->in_reply_to ?: $message->message_id)
                    ->orWhere('in_reply_to', $message->message_id);

                if ($message->in_reply_to) {
                    $query->orWhere('message_id', $message->in_reply_to);
                }
            })
            ->oldest('created_at')
            ->limit(20)
            ->get();
    }
}
