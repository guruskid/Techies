<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::with('user');

        if ($request->filled('action')) {
            $query->where('action', 'like', '%'.$request->action.'%');
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        return view('super-admin.audit-logs.index', [
            'logs' => $query->latest()->paginate(30)->withQueryString(),
            'action' => $request->action,
        ]);
    }
}
