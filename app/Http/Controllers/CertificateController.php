<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\View\View;

class CertificateController extends Controller
{
    public function show(Certificate $certificate): View
    {
        $certificate->load('user', 'event', 'issuer');

        return view('pages.certificate', compact('certificate'));
    }
}
