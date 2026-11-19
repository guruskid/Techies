<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Project;
use App\Models\UserProfile;
use App\Models\WorkExperience;
use App\Support\Username;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MyProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        $profile = $user->ensureProfile();
        $chapters = Chapter::orderBy('name')->get();
        $selectedChapter = $user->chapters()->first();

        return view('member.profile', compact('user', 'profile', 'chapters', 'selectedChapter'));
    }

    public function checkUsername(Request $request): JsonResponse
    {
        return response()->json(
            Username::availability($request->query('username'), auth()->user()->profile?->id)
        );
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $profile = $user->profile ?? new UserProfile(['user_id' => $user->id]);

        $request->merge([
            'username' => Username::normalize($request->input('username')) ?: null,
        ]);

        $data = $request->validate([
            'username' => Username::rules($profile->id),
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'headline' => 'nullable|string|max:200',
            'professional_title' => 'nullable|string|max:150',
            'technology_category' => 'nullable|string|max:100',
            'experience_level' => 'nullable|string|max:50',
            'current_organization' => 'nullable|string|max:150',
            'education' => 'nullable|string|max:500',
            'bio' => 'nullable|string|max:1000',
            'country' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'linkedin' => 'nullable|url|max:255',
            'github' => 'nullable|url|max:255',
            'facebook' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'tiktok' => 'nullable|url|max:255',
            'avatar' => 'nullable|image|max:2048',
            'is_public' => 'nullable|boolean',
            'weekly_digest' => 'nullable|boolean',
            'chapter_id' => 'nullable|exists:chapters,id',
            'interests' => 'nullable|array',
            'interests.*' => 'exists:interests,id',
            'likes' => 'nullable|string|max:500',
            'certifications' => 'nullable|string|max:1000',
            'languages' => 'nullable|string|max:500',
            'availability' => 'nullable|string|max:100',
            'skills' => 'nullable|array',
            'skills.*' => 'exists:skills,id',
        ]);

        unset($data['avatar']);

        $username = $data['username'] ?? null;
        unset($data['username']);

        $profile->fill($data);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $profile->avatar = '/storage/'.$path;
        }

        // Fields not present in the request (e.g. a removed social link) should be cleared.
        foreach (['linkedin', 'github', 'facebook', 'twitter', 'tiktok'] as $social) {
            $profile->{$social} = $data[$social] ?? null;
        }

        if (filled($username)) {
            $profile->username = $username;
        }

        if (blank($profile->username)) {
            $profile->username = Username::generate(
                trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? '')) ?: 'user'.$user->id
            );
        }

        $profile->is_public = $request->boolean('is_public');
        $profile->weekly_digest = $request->boolean('weekly_digest');
        $profile->profile_completion = $profile->completionPercentage();
        $profile->save();

        // Chapter membership is additive: choosing a chapter joins it without
        // removing memberships gained from the chapter pages.
        if ($request->filled('chapter_id')) {
            $user->chapters()->syncWithoutDetaching([$request->input('chapter_id')]);
        }

        $user->skills()->sync($request->input('skills', []));
        $user->interests()->sync($request->input('interests', []));

        // Replace work experience entries.
        $user->workExperiences()->delete();
        foreach ($request->input('work_title', []) as $i => $title) {
            if (blank($title)) {
                continue;
            }

            WorkExperience::create([
                'user_id' => $user->id,
                'title' => $title,
                'company' => $request->input('work_company.'.$i),
                'location' => $request->input('work_location.'.$i),
                'start_date' => $request->input('work_start.'.$i) ?: null,
                'end_date' => $request->input('work_end.'.$i) ?: null,
                'is_current' => $request->boolean('work_current.'.$i),
                'description' => $request->input('work_desc.'.$i),
            ]);
        }

        // Replace project entries.
        $user->projects()->delete();
        foreach ($request->input('project_title', []) as $i => $title) {
            if (blank($title)) {
                continue;
            }

            Project::create([
                'user_id' => $user->id,
                'title' => $title,
                'description' => $request->input('project_desc.'.$i),
                'url' => $request->input('project_url.'.$i),
                'technologies' => $request->input('project_tech.'.$i),
            ]);
        }

        if ($request->filled('first_name') || $request->filled('last_name')) {
            $user->name = trim(($request->input('first_name') ?? '').' '.($request->input('last_name') ?? '')) ?: $user->name;
            $user->save();
        }

        return back()->with('success', 'Profile updated successfully.');
    }
}
