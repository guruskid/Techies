<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;

class HubDashboardController extends Controller
{
    public function index()
    {
        return view('hub.dashboard');
    }
}
