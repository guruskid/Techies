<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        $members = $q->latest()->paginate(20);

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

        return back()->with('success', 'Member suspended.');
    }

    public function restore(User $user)
    {
        $user->update(['status' => 'active']);

        return back()->with('success', 'Member restored.');
    }
}
