<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;

class EventController extends Controller
{
    public function show($slug)
    {
        $event = Event::where('slug', $slug)->with('category', 'organizer', 'hub', 'chapter')->firstOrFail();

        return view('pages.event-detail', compact('event'));
    }
}
