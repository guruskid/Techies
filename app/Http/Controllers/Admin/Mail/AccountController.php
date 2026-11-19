<?php

namespace App\Http\Controllers\Admin\Mail;

use App\Http\Controllers\Controller;
use App\Models\EmailAccount;
use App\Models\EmailMessage;
use App\Models\User;
use App\Services\Mail\MailAccountMailer;
use App\Services\Mail\MailboxSynchronizer;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class AccountController extends Controller
{
    public function index(): View
    {
        return view('admin.mail.accounts.index', [
            'accounts' => EmailAccount::visibleTo(auth()->user())
                ->withCount(['messages', 'broadcasts', 'sharedUsers'])
                ->orderByDesc('is_default')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.mail.accounts.form', ['account' => new EmailAccount]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $shared = $this->sharedUsersFrom($data);
        $data['created_by'] = auth()->id();
        $data['is_default'] = $request->boolean('is_default') || EmailAccount::count() === 0;

        $account = EmailAccount::create($data);

        if ($request->hasAny(['shared_users', 'manager_users'])) {
            $account->sharedUsers()->sync($shared);
        }

        AuditLogger::log('mail.account.created', $account, ['email' => $account->email]);

        return redirect()
            ->route('admin.mail.accounts.edit', $account)
            ->with('success', 'Email account connected. Send a test email to confirm the SMTP settings.');
    }

    public function edit(EmailAccount $account): View
    {
        abort_unless($account->canBeManagedBy(auth()->user()), 403);

        return view('admin.mail.accounts.form', [
            'account' => $account,
            'admins' => User::where('status', 'active')
                ->whereIn('role', ['admin', 'super_admin'])
                ->whereKeyNot(auth()->id())
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
            'sharedViewers' => $account->sharedUsers()
                ->wherePivot('can_manage', false)
                ->pluck('users.id')
                ->all(),
            'sharedManagers' => $account->sharedUsers()
                ->wherePivot('can_manage', true)
                ->pluck('users.id')
                ->all(),
            'inbox' => $account->messages()
                ->where('direction', EmailMessage::DIRECTION_INBOUND)
                ->latest('received_at')
                ->limit(5)
                ->get(),
        ]);
    }

    public function update(Request $request, EmailAccount $account): RedirectResponse
    {
        abort_unless($account->canBeManagedBy(auth()->user()), 403);

        $data = $this->validated($request, $account);
        $shared = $this->sharedUsersFrom($data);

        if ($request->boolean('is_default')) {
            $data['is_default'] = true;
        } elseif ($account->is_default) {
            unset($data['is_default']);
        }

        $account->update($data);

        if ($request->hasAny(['shared_users', 'manager_users'])) {
            $account->sharedUsers()->sync($shared);
        }

        AuditLogger::log('mail.account.updated', $account, ['email' => $account->email]);

        return back()->with('success', 'Email account updated.');
    }

    public function destroy(EmailAccount $account): RedirectResponse
    {
        abort_unless($account->canBeManagedBy(auth()->user()), 403);

        $wasDefault = $account->is_default;
        $account->delete();

        if ($wasDefault) {
            EmailAccount::where('is_active', true)->orderBy('id')->first()?->update(['is_default' => true]);
        }

        AuditLogger::log('mail.account.deleted', null, ['email' => $account->email]);

        return redirect()->route('admin.mail.accounts.index')->with('success', 'Email account removed.');
    }

    public function makeDefault(EmailAccount $account): RedirectResponse
    {
        abort_unless($account->canBeManagedBy(auth()->user()), 403);

        $account->update(['is_default' => true, 'is_active' => true]);

        return back()->with('success', $account->name.' is now the default sending account.');
    }

    public function test(Request $request, EmailAccount $account, MailAccountMailer $mailer): RedirectResponse
    {
        abort_unless($account->canBeManagedBy(auth()->user()), 403);

        $data = $request->validate([
            'test_email' => ['required', 'email'],
        ]);

        try {
            $mailer->sendTest($account, $data['test_email']);
        } catch (Throwable $exception) {
            return back()->with('error', 'Test failed: '.$exception->getMessage());
        }

        AuditLogger::log('mail.account.tested', $account, ['to' => $data['test_email']]);

        return back()->with('success', 'Test email sent to '.$data['test_email'].'.');
    }

    public function sync(EmailAccount $account, MailboxSynchronizer $synchronizer): RedirectResponse
    {
        abort_unless($account->canBeManagedBy(auth()->user()), 403);

        try {
            $count = $synchronizer->sync($account);
        } catch (Throwable $exception) {
            return back()->with('error', 'Inbox sync failed: '.$exception->getMessage());
        }

        return back()->with('success', "Inbox synced — {$count} new message(s).");
    }

    public function testImap(EmailAccount $account, MailboxSynchronizer $synchronizer): RedirectResponse
    {
        abort_unless($account->canBeManagedBy(auth()->user()), 403);

        try {
            $synchronizer->testConnection($account);
        } catch (Throwable $exception) {
            return back()->with('error', 'IMAP test failed: '.$exception->getMessage());
        }

        AuditLogger::log('mail.account.imap_tested', $account, ['email' => $account->email]);

        return back()->with('success', 'IMAP connection succeeded for '.$account->email.'.');
    }

    public function rotateInbound(EmailAccount $account): RedirectResponse
    {
        abort_unless($account->canBeManagedBy(auth()->user()), 403);

        $account->rotateInboundCredentials();

        AuditLogger::log('mail.account.inbound_rotated', $account, ['email' => $account->email]);

        return back()->with('success', 'Inbound webhook URL and signing secret rotated. Update your mail provider with the new values.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?EmailAccount $account = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:150'],
            'reply_to' => ['nullable', 'email', 'max:255'],
            'signature' => ['nullable', 'string', 'max:2000'],
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'integer', 'between:1,65535'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:2000'],
            'smtp_encryption' => ['required', Rule::in(['tls', 'ssl', 'none'])],
            'imap_host' => ['nullable', 'string', 'max:255'],
            'imap_port' => ['nullable', 'integer', 'between:1,65535'],
            'imap_username' => ['nullable', 'string', 'max:255'],
            'imap_password' => ['nullable', 'string', 'max:2000'],
            'imap_encryption' => ['required', Rule::in(['tls', 'ssl', 'none'])],
            'requires_signature' => ['nullable', 'boolean'],
            'shared_users' => ['nullable', 'array'],
            'shared_users.*' => ['integer', 'exists:users,id'],
            'manager_users' => ['nullable', 'array'],
            'manager_users.*' => ['integer', 'exists:users,id'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['requires_signature'] = $request->boolean('requires_signature');

        foreach (['smtp_password', 'imap_password'] as $field) {
            if (blank($data[$field] ?? null)) {
                unset($data[$field]);
            }
        }

        return $data;
    }

    /**
     * Build the access pivot from the sharing checkboxes.
     *
     * @param  array<string, mixed>  $data
     * @return array<int, array{can_manage: bool}>
     */
    private function sharedUsersFrom(array &$data): array
    {
        $viewers = collect($data['shared_users'] ?? [])
            ->mapWithKeys(fn ($id) => [(int) $id => ['can_manage' => false]]);

        $managers = collect($data['manager_users'] ?? [])
            ->mapWithKeys(fn ($id) => [(int) $id => ['can_manage' => true]]);

        unset($data['shared_users'], $data['manager_users']);

        return $viewers->merge($managers)->all();
    }
}
