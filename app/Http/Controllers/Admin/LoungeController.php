<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoungePost;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LoungeController extends Controller
{
    public function index(): View
    {
        return view('admin.lounge.index', [
            'posts' => LoungePost::with('user')->withCount('replies')->latest()->paginate(30),
        ]);
    }

    public function togglePin(LoungePost $post): RedirectResponse
    {
        $post->update(['is_pinned' => ! $post->is_pinned]);

        return back()->with('success', 'Post updated.');
    }

    public function destroy(LoungePost $post): RedirectResponse
    {
        $post->delete();
        AuditLogger::log('lounge.post.deleted', $post, ['title' => $post->title]);

        return back()->with('success', 'Post deleted.');
    }
}
