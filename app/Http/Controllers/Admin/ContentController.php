<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Notifications\ContentApproved;
use App\Notifications\ContentRejected;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Article::with('author', 'reviewer');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('topic')) {
            $query->where('topic', $request->topic);
        }
        if ($request->filled('approval')) {
            $superAdminOnly = collect(Article::topics())
                ->filter(fn (array $topic) => $topic['approval'] === 'super_admin')
                ->keys()
                ->all();

            if ($request->approval === 'super_admin') {
                $query->whereIn('topic', $superAdminOnly);
            } elseif ($request->approval === 'admin') {
                $query->whereNotIn('topic', $superAdminOnly);
            }
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhereHas('author', fn ($author) => $author->where('name', 'like', "%{$search}%"));
            });
        }

        $articles = $query->latest('updated_at')->paginate(20)->withQueryString();

        $counts = [
            'pending' => Article::pending()->count(),
            'approved' => Article::approved()->count(),
            'rejected' => Article::where('status', Article::STATUS_REJECTED)->count(),
            'draft' => Article::where('status', Article::STATUS_DRAFT)->count(),
            'super_admin_pending' => Article::pending()
                ->whereIn('topic', collect(Article::topics())
                    ->filter(fn (array $topic) => $topic['approval'] === 'super_admin')
                    ->keys())
                ->count(),
        ];

        return view('admin.content.index', [
            'articles' => $articles,
            'counts' => $counts,
            'topics' => Article::topics(),
        ]);
    }

    public function show(Article $article): View
    {
        $article->load('author', 'reviewer');

        return view('admin.content.show', compact('article'));
    }

    public function approve(Request $request, Article $article): RedirectResponse
    {
        $this->authorizeReviewer($article);

        abort_unless(
            in_array($article->status, [Article::STATUS_PENDING, Article::STATUS_REJECTED], true),
            403,
            'Only submitted articles can be approved.'
        );

        $data = $request->validate([
            'review_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $article->update([
            'status' => Article::STATUS_APPROVED,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'published_at' => $article->published_at ?? now(),
            'review_notes' => $data['review_notes'] ?? null,
        ]);

        AuditLogger::log('content.approved', $article, ['title' => $article->title, 'topic' => $article->topic]);

        $article->author?->notify(new ContentApproved($article));

        return back()->with('success', 'Article approved and published.');
    }

    public function reject(Request $request, Article $article): RedirectResponse
    {
        $this->authorizeReviewer($article);

        abort_unless(
            in_array($article->status, [Article::STATUS_PENDING, Article::STATUS_REJECTED], true),
            403,
            'Only submitted articles can be sent back for changes.'
        );

        $data = $request->validate([
            'review_notes' => ['required', 'string', 'max:2000'],
        ], [
            'review_notes.required' => 'Tell the author what needs to change.',
        ]);

        $article->update([
            'status' => Article::STATUS_REJECTED,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => $data['review_notes'],
        ]);

        AuditLogger::log('content.changes_requested', $article, ['notes' => $data['review_notes']]);

        $article->author?->notify(new ContentRejected($article));

        return back()->with('success', 'Changes requested. The author can now revise the article.');
    }

    public function unpublish(Request $request, Article $article): RedirectResponse
    {
        $this->authorizeReviewer($article);

        abort_unless($article->status === Article::STATUS_APPROVED, 403, 'Only published articles can be unpublished.');

        $data = $request->validate([
            'review_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $article->update([
            'status' => Article::STATUS_DRAFT,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'published_at' => null,
            'review_notes' => $data['review_notes'] ?? $article->review_notes,
        ]);

        AuditLogger::log('content.unpublished', $article, ['title' => $article->title]);

        return back()->with('success', 'Article unpublished and returned to the author.');
    }

    public function forcePublish(Article $article): RedirectResponse
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        $article->update([
            'status' => Article::STATUS_APPROVED,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'published_at' => now(),
        ]);

        AuditLogger::log('content.force_published', $article, ['title' => $article->title]);

        return back()->with('success', 'Article force published by super admin override.');
    }

    private function authorizeReviewer(Article $article): void
    {
        abort_unless(
            $article->canBeApprovedBy(auth()->user()),
            403,
            'This topic can only be reviewed by a super administrator.'
        );
    }
}
