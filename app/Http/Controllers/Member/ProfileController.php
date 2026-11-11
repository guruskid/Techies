<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;

class ProfileController extends Controller
{
    public function show($username)
    {
        $profile = UserProfile::where('username', $username)->first();
        $user = null;
        if ($profile && $profile->user) {
            $user = $profile->user;
        } else {
            // fallback by user id
            $user = User::find($username);
            if ($user) {
                $profile = $user->profile;
                if (! $profile) {
                    $profile = new UserProfile(['user_id' => $user->id, 'username' => 'user'.$user->id, 'is_public' => true]);
                }
            }
        }
        if (! $user) {
            abort(404);
        }
        if (! $profile) {
            $profile = new UserProfile(['user_id' => $user->id, 'username' => 'user'.$user->id, 'is_public' => true]);
        }
        if (! $profile->is_public && (! auth()->check() || auth()->id() !== $user->id)) {
            abort(403, 'This profile is private.');
        }

        return view('pages.member-profile', compact('user', 'profile'));
    }
}
