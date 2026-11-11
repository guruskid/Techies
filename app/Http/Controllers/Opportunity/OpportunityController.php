<?php

namespace App\Http\Controllers\Opportunity;

use App\Http\Controllers\Controller;
use App\Models\Opportunity;

class OpportunityController extends Controller
{
    public function show($slug)
    {
        $opportunity = Opportunity::where('slug', $slug)->with('category', 'hub')->firstOrFail();

        return view('pages.opportunity-detail', compact('opportunity'));
    }
}
