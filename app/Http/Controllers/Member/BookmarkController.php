<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Opportunity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookmarkController extends Controller
{
    public function index(Request $request): View
    {
        $bookmarks = $request->user()->bookmarks()
            ->with('bookmarkable')
            ->latest()
            ->paginate(15);

        return view('member.bookmarks.index', compact('bookmarks'));
    }

    public function toggle(Request $request, string $type, int $id): RedirectResponse
    {
        $model = match ($type) {
            'event' => Event::where('status', 'published')->findOrFail($id),
            'opportunity' => Opportunity::where('status', 'published')->findOrFail($id),
            default => abort(404),
        };

        $bookmark = $request->user()->bookmarks()
            ->where('bookmarkable_type', $model::class)
            ->where('bookmarkable_id', $model->getKey())
            ->first();

        if ($bookmark) {
            $bookmark->delete();

            return back()->with('success', 'Removed from your saved items.');
        }

        $request->user()->bookmarks()->create([
            'bookmarkable_type' => $model::class,
            'bookmarkable_id' => $model->getKey(),
        ]);

        return back()->with('success', 'Saved to your items.');
    }

    public function destroy(Request $request, int $bookmarkId): RedirectResponse
    {
        $request->user()->bookmarks()->whereKey($bookmarkId)->firstOrFail()->delete();

        return back()->with('success', 'Removed from your saved items.');
    }
}
