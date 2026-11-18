<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use App\Notifications\MemberContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(string $username): View
    {
        return $this->render(UserProfile::where('username', $username)->firstOrFail(), 403);
    }

    public function showBySubdomain(string $username): View
    {
        $profile = UserProfile::where('username', strtolower($username))->firstOrFail();

        abort_if($profile->isReservedUsername($profile->username), 404);

        // Private profiles are never exposed on a public subdomain.
        return $this->render($profile, 404);
    }

    /**
     * Relay a member-to-member message by email, using the sender as reply-to.
     */
    public function contact(Request $request, string $username): RedirectResponse
    {
        $profile = UserProfile::where('username', $username)->firstOrFail();
        $recipient = $profile->user;

        abort_unless($recipient && $recipient->status === 'active', 404);
        abort_if($recipient->id === auth()->id(), 403, 'You cannot send a message to yourself.');

        // Silently drop obvious bot submissions.
        if ($request->filled('website')) {
            return back()->with('success', 'Your message has been sent.');
        }

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $recipient->notify(new MemberContactMessage(
            auth()->user(),
            $data['subject'],
            $data['message'],
        ));

        return back()->with('success', 'Your message was sent to '.$recipient->name.'. They can reply straight to your email.');
    }

    private function render(UserProfile $profile, int $privateStatus): View
    {
        $user = $profile->user;

        if (! $user || $user->status !== 'active') {
            abort(404);
        }

        if (! $profile->is_public && (! auth()->check() || auth()->id() !== $user->id)) {
            abort($privateStatus);
        }

        return view('pages.member-profile', compact('user', 'profile'));
    }
}
