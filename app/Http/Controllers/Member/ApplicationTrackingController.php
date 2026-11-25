<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\GrantApplication;
use App\Models\MentorshipRequest;
use App\Models\OpportunityApplication;
use App\Models\SpeakerSubmission;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationTrackingController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $opportunityApplications = OpportunityApplication::where('user_id', $user->id)
            ->with('opportunity')
            ->latest('applied_at')
            ->paginate(8, ['*'], 'opportunities_page');
        $grantApplications = GrantApplication::where('user_id', $user->id)
            ->with('grant')
            ->latest()
            ->paginate(8, ['*'], 'grants_page');
        $speakerSubmissions = SpeakerSubmission::where('user_id', $user->id)
            ->with('speakerCall')
            ->latest()
            ->paginate(8, ['*'], 'speakers_page');
        $mentorshipRequest = MentorshipRequest::where('user_id', $user->id)->with('match')->first();

        return view('member.applications.index', compact(
            'opportunityApplications',
            'grantApplications',
            'speakerSubmissions',
            'mentorshipRequest',
        ));
    }
}
