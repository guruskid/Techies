<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MemberFeedback;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(): View
    {
        $feedback = MemberFeedback::with('user')->latest('submitted_at')->paginate(30);
        $average = round((float) MemberFeedback::avg('rating'), 1);

        return view('admin.feedback.index', compact('feedback', 'average'));
    }
}
