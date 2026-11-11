<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Project;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\WorkExperience;
use Illuminate\Http\Request;

class MyProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        $profile = $user->profile ?? new UserProfile(['user_id' => $user->id]);
        $chapters = Chapter::orderBy('name')->get();
        $selectedChapter = $user->chapters()->first();

        return view('member.profile', compact('user', 'profile', 'chapters', 'selectedChapter'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
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
            'chapter_id' => 'nullable|exists:chapters,id',
            'interests' => 'nullable|array',
            'interests.*' => 'exists:interests,id',
            'likes' => 'nullable|string|max:500',
            'certifications' => 'nullable|string|max:1000',
            'languages' => 'nullable|string|max:500',
            'availability' => 'nullable|string|max:100',
            'headline' => 'nullable|string|max:200',
            'skills' => 'nullable|array',
            'skills.*' => 'exists:skills,id',
        ]);
        $profile = UserProfile::firstOrCreate(['user_id' => $user->id]);
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $profile->avatar = '/storage/'.$path;
        }
        $profile->fill($data);
        foreach (['linkedin', 'github', 'portfolio', 'website'] as $soc) {
            if (! $request->boolean('has_'.$soc)) {
                $profile->$soc = null;
            }
        }
        if (empty($profile->username)) {
            $profile->username = 'user'.$user->id;
        }
        $profile->is_public = $request->boolean('is_public');
        // profile completion calc
        $fields = ['first_name', 'last_name', 'phone', 'professional_title', 'technology_category', 'experience_level', 'current_organization', 'education', 'bio', 'country', 'state', 'city', 'linkedin', 'github', 'portfolio', 'website', 'facebook', 'twitter', 'tiktok'];
        $filled = 0;
        foreach ($fields as $f) {
            if (! empty($profile->$f)) {
                $filled++;
            }
        }
        $profile->profile_completion = (int) round(($filled / count($fields)) * 100);
        $profile->save();
        // sync chapter membership
        if ($request->filled('chapter_id')) {
            User::find(auth()->id())->chapters()->sync([$request->chapter_id]);
        } else {
            User::find(auth()->id())->chapters()->detach();
        }
        // skills
        $user->skills()->sync($request->input('skills', []));
        $user->interests()->sync($request->input('interests', []));
        $profile->likes = $request->input('likes');
        // work experiences
        $user->workExperiences()->delete();
        foreach ($request->input('work_title', []) as $i => $title) {
            if (empty($title)) {
                continue;
            }
            WorkExperience::create([
                'user_id' => $user->id,
                'title' => $title,
                'company' => $request->input('work_company.'.$i),
                'location' => $request->input('work_location.'.$i),
                'start_date' => $request->input('work_start.'.$i) ?: null,
                'end_date' => $request->input('work_end.'.$i) ?: null,
                'is_current' => (bool) $request->input('work_current.'.$i),
                'description' => $request->input('work_desc.'.$i),
            ]);
        }
        // projects
        $user->projects()->delete();
        foreach ($request->input('project_title', []) as $i => $title) {
            if (empty($title)) {
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
        // update user name
        if ($request->filled('first_name') || $request->filled('last_name')) {
            $user->name = trim(($request->first_name ?? '').' '.($request->last_name ?? '')) ?: $user->name;
            $user->save();
        }

        return back()->with('success', 'Profile updated successfully.');
    }
}
