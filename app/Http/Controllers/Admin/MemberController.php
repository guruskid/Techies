<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\MembershipActivated;
use App\Support\AuditLogger;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $q = User::where('role', 'member')->with('profile');
        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function ($w) use ($s) {
                $w->where('name', 'like', "%$s%")
                    ->orWhere('email', 'like', "%$s%");
            });
        }
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }
        if ($request->filled('membership')) {
            $q->where('membership_status', $request->membership);
        }
        $members = $q->latest()->paginate(20)->withQueryString();

        return view('admin.members.index', compact('members'));
    }

    public function show(User $user)
    {
        $user->load('profile', 'skills', 'interests');

        return view('admin.members.show', compact('user'));
    }

    public function suspend(User $user)
    {
        if ($user->id === auth()->id() || $user->hasRole('super_admin')) {
            return back()->with('error', 'You cannot suspend this account.');
        }

        $user->update(['status' => 'suspended']);

        AuditLogger::log('member.suspended', $user, ['name' => $user->name]);

        return back()->with('success', 'Member suspended.');
    }

    public function restore(User $user)
    {
        $user->update(['status' => 'active']);

        AuditLogger::log('member.restored', $user, ['name' => $user->name]);

        return back()->with('success', 'Member restored.');
    }

    public function confirmMembership(User $user)
    {
        $user->update([
            'membership_status' => 'active',
            'membership_paid_at' => now(),
            'membership_confirmed_by' => auth()->id(),
        ]);

        AuditLogger::log('membership.confirmed', $user, ['name' => $user->name]);

        $user->notify(new MembershipActivated($user));

        return back()->with('success', 'Membership confirmed. The member has been emailed their welcome guide.');
    }

    public function waiveMembership(User $user)
    {
        $user->update([
            'membership_status' => 'exempt',
            'membership_paid_at' => now(),
            'membership_confirmed_by' => auth()->id(),
        ]);

        AuditLogger::log('membership.waived', $user, ['name' => $user->name]);

        $user->notify(new MembershipActivated($user));

        return back()->with('success', 'Membership fee waived. The member has been emailed their welcome guide.');
    }
}
