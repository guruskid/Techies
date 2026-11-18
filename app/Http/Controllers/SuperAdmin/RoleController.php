<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::with('permissions')
            ->withCount('users')
            ->orderBy('name')
            ->get();

        return view('super-admin.roles.index', compact('roles'));
    }
}
