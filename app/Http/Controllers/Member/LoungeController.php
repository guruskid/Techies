<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\LoungePost;
use App\Models\LoungeReply;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoungeController extends Controller
{
    public function index(): View
    {
        $posts = LoungePost::with('user')->withCount('replies')
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate(20);

        return view('member.lounge.index', compact('posts'));
    }

    public function show(LoungePost $post): View
    {
        $post->load(['user', 'replies.user']);

        return view('member.lounge.show', compact('post'));
    }

    public function create(): View
    {
        return view('member.lounge.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'category' => ['required', 'string', 'max:80'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $post = LoungePost::create($data + ['user_id' => $request->user()->id]);

        return redirect()->route('lounge.show', $post)->with('success', 'Post published.');
    }

    public function reply(Request $request, LoungePost $post): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:3000']]);

        LoungeReply::create([
            'lounge_post_id' => $post->id,
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        return back()->with('success', 'Reply added.');
    }
}
