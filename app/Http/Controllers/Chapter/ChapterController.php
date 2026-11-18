<?php

namespace App\Http\Controllers\Chapter;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Notifications\ChapterJoined;
use App\Notifications\ChapterMemberJoined;
use Illuminate\Http\RedirectResponse;

class ChapterController extends Controller
{
    public function show($slug)
    {
        $chapter = Chapter::where('slug', $slug)->where('status', 'active')->with('coordinator')->firstOrFail();

        $events = $chapter->events()
            ->where('status', 'published')
            ->where('start_date', '>=', now()->toDateString())
            ->orderBy('start_date')
            ->take(6)
            ->get();

        $isMember = auth()->check() && $chapter->members()->whereKey(auth()->id())->exists();

        return view('pages.chapter-detail', compact('chapter', 'events', 'isMember'));
    }

    public function join(Chapter $chapter): RedirectResponse
    {
        abort_unless($chapter->status === 'active', 404);

        $user = auth()->user();

        if ($chapter->members()->whereKey($user->id)->exists()) {
            return back()->with('status', 'You are already a member of '.$chapter->name.'.');
        }

        $chapter->members()->attach($user->id);

        $user->notify(new ChapterJoined($chapter));

        if ($chapter->coordinator && $chapter->coordinator->id !== $user->id) {
            $chapter->coordinator->notify(new ChapterMemberJoined($chapter, $user));
        }

        return back()->with('success', 'You joined '.$chapter->name.'. Check your email for the details.');
    }

    public function leave(Chapter $chapter): RedirectResponse
    {
        abort_unless($chapter->status === 'active', 404);

        $chapter->members()->detach(auth()->id());

        return back()->with('success', 'You left '.$chapter->name.'.');
    }
}
