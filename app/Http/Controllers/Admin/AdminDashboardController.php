<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Event;
use App\Models\Hub;
use App\Models\Opportunity;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'members' => User::count(),
            'hubs' => Hub::count(),
            'chapters' => Chapter::count(),
            'events' => Event::count(),
            'opportunities' => Opportunity::count(),
            'pending_hubs' => Hub::where('status', 'pending')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
