<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;
use App\Models\Hub;

class HubController extends Controller
{
    public function show($slug)
    {
        $hub = Hub::where('slug', $slug)->firstOrFail();

        return view('pages.hub-profile', compact('hub'));
    }
}
