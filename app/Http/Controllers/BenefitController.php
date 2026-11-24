<?php

namespace App\Http\Controllers;

use App\Models\MemberBenefit;
use Illuminate\View\View;

class BenefitController extends Controller
{
    public function index(): View
    {
        $benefits = MemberBenefit::active()
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->groupBy('category');

        return view('pages.benefits', compact('benefits'));
    }
}
