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
            if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            }
            if ($user->hasRole('hub_owner')) {
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
