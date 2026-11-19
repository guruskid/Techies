<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with('roles', 'profile');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($request->filled('role')) {
            $query->whereHas('roles', fn ($roles) => $roles->where('name', $request->role));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return view('super-admin.users.index', [
            'users' => $query->latest()->paginate(20)->withQueryString(),
            'roles' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', Rule::exists('roles', 'name')],
        ]);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own role.');
        }

        if ($user->isSuperAdmin() && $data['role'] !== 'super_admin' && User::role('super_admin')->count() <= 1) {
            return back()->with('error', 'The last super administrator must keep that role.');
        }

        $previousRole = $user->role;

        // users.role holds the primary staff role. Extra Spatie roles (such as
        // hub_owner) are capabilities, so changing the primary role must not
        // silently strip them.
        $extraRoles = $user->roles
            ->pluck('name')
            ->reject(fn (string $name) => in_array($name, ['member', 'admin', 'super_admin'], true))
            ->unique()
            ->values()
            ->all();

        $user->syncRoles(array_values(array_unique(array_merge([$data['role']], $extraRoles))));
        $user->update(['role' => $data['role']]);

        AuditLogger::log('user.role_changed', $user, [
            'from' => $previousRole,
            'to' => $data['role'],
        ]);

        return back()->with('success', $user->name.' is now a '.str_replace('_', ' ', $data['role']).'.');
    }

    public function suspend(User $user): RedirectResponse
    {
        if ($user->id === auth()->id() || $user->isSuperAdmin()) {
            return back()->with('error', 'You cannot suspend this account.');
        }

        $user->update(['status' => 'suspended']);

        AuditLogger::log('user.suspended', $user, ['name' => $user->name]);

        return back()->with('success', $user->name.' has been suspended.');
    }

    public function restore(User $user): RedirectResponse
    {
        $user->update(['status' => 'active']);

        AuditLogger::log('user.restored', $user, ['name' => $user->name]);

        return back()->with('success', $user->name.' has been restored.');
    }
}
