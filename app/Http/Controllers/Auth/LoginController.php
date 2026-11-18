<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $creds = $request->validate(['email' => 'required|email', 'password' => 'required']);
        if (Auth::attempt($creds, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->status !== 'active') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors(['email' => 'Your account has been suspended. Please contact support.'])->onlyInput('email');
            }

            if ($user->isSuperAdmin()) {
                return redirect()->route('super-admin.dashboard');
            }
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            if ($user->hasRole('hub_owner') || $user->hub()->exists()) {
                return redirect()->route('hub.dashboard');
            }

            return redirect()->route('dashboard');
        }

        return back()->withErrors(['email' => 'Invalid credentials'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
