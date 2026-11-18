<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\Hub;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function index(): View
    {
        $superAdminTopics = collect(Article::topics())
            ->filter(fn (array $topic) => $topic['approval'] === 'super_admin')
            ->keys();

        $stats = [
            'total_users' => User::count(),
            'active_members' => User::where('role', 'member')->where('status', 'active')->count(),
            'staff' => User::whereIn('role', ['admin', 'super_admin'])->count(),
            'suspended' => User::where('status', 'suspended')->count(),
            'pending_content' => Article::pending()->count(),
            'super_admin_content' => Article::pending()->whereIn('topic', $superAdminTopics)->count(),
            'published_content' => Article::approved()->count(),
            'pending_hubs' => Hub::where('status', 'pending')->count(),
            'events' => Event::count(),
            'opportunities' => Opportunity::count(),
        ];

        $approvalQueue = Article::pending()
            ->with('author')
            ->whereIn('topic', $superAdminTopics)
            ->oldest('submitted_at')
            ->take(6)
            ->get();

        $recentUsers = User::with('roles')->latest()->take(6)->get();

        $roles = Role::withCount('users')->orderBy('name')->get();

        $recentLogs = AuditLog::with('user')->latest()->take(8)->get();

        return view('super-admin.dashboard', compact('stats', 'approvalQueue', 'recentUsers', 'roles', 'recentLogs'));
    }
}
