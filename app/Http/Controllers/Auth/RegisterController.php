<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Support\Username;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        if ($request->filled('referral_code')) {
            $request->merge(['referral_code' => strtoupper((string) $request->input('referral_code'))]);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'referral_code' => ['nullable', 'string', 'max:20', Rule::exists('users', 'referral_code')],
        ], [
            'email.unique' => 'An account with this email already exists. Please log in or use a different email.',
            'referral_code.exists' => 'That referral code is not valid.',
        ]);

        $referralCode = $data['referral_code'] ?? null;
        $referrer = $referralCode
            ? User::where('referral_code', strtoupper($referralCode))->first()
            : null;

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'member',
            'status' => 'active',
            'membership_status' => 'unpaid',
            'referred_by' => $referrer?->id,
        ]);
        $user->forceFill(['email_verified_at' => null])->save();
        Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
        $user->assignRole('member');
        UserProfile::create([
            'user_id' => $user->id,
            'username' => Username::generate($data['name']),
            'is_public' => false,
        ]);
        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
