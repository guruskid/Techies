<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventCategory;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function index(): View
    {
        $categories = EventCategory::whereIn('name', ['Career Clinics', 'Office Hours'])->pluck('id');

        $events = Event::with('category')
            ->where('status', 'published')
            ->whereIn('category_id', $categories)
            ->whereDate('start_date', '>=', today())
            ->orderBy('start_date')
            ->limit(8)
            ->get();

        return view('pages.programs', compact('events'));
    }
}
