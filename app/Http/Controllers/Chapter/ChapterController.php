<?php

namespace App\Http\Controllers\Chapter;

use App\Http\Controllers\Controller;
use App\Models\Chapter;

class ChapterController extends Controller
{
    public function show($slug)
    {
        $chapter = Chapter::where('slug', $slug)->with('coordinator')->firstOrFail();

        return view('pages.chapter-detail', compact('chapter'));
    }
}
