<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\User;
use App\Notifications\ContentSubmitted;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContentController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $articles = $user->articles()
            ->with('reviewer')
            ->latest()
            ->paginate(10);

        $counts = [
            'total' => $user->articles()->count(),
            'drafts' => $user->articles()->where('status', Article::STATUS_DRAFT)->count(),
            'pending' => $user->articles()->where('status', Article::STATUS_PENDING)->count(),
            'approved' => $user->articles()->where('status', Article::STATUS_APPROVED)->count(),
            'rejected' => $user->articles()->where('status', Article::STATUS_REJECTED)->count(),
        ];

        return view('member.content.index', compact('articles', 'counts'));
    }

    public function create(): View
    {
        return view('member.content.create', [
            'article' => new Article,
            'topics' => Article::topics(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $action = $data['action'] ?? 'save';

        $article = auth()->user()->articles()->create(array_merge(
            Arr::except($data, ['action']),
            ['status' => Article::STATUS_DRAFT]
        ));

        if ($action === 'submit') {
            return $this->submitForReview($article);
        }

        return redirect()
            ->route('member.content.edit', $article)
            ->with('success', 'Draft saved. Add your SEO details, then submit it for review.');
    }

    public function edit(Article $article): View
    {
        $this->authorizeOwner($article);

        abort_unless($article->isEditableBy(auth()->user()), 403, 'This article can no longer be edited.');

        return view('member.content.edit', [
            'article' => $article,
            'topics' => Article::topics(),
        ]);
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $this->authorizeOwner($article);

        abort_unless($article->isEditableBy(auth()->user()), 403, 'This article can no longer be edited.');

        $data = $this->validated($request, $article);
        $action = $data['action'] ?? 'save';

        $article->update(array_merge(
            Arr::except($data, ['action']),
            ['status' => Article::STATUS_DRAFT]
        ));

        if ($action === 'submit') {
            return $this->submitForReview($article);
        }

        return redirect()
            ->route('member.content.edit', $article)
            ->with('success', 'Draft updated.');
    }

    public function submit(Article $article): RedirectResponse
    {
        $this->authorizeOwner($article);

        abort_unless($article->isEditableBy(auth()->user()), 403, 'This article can no longer be submitted.');

        return $this->submitForReview($article);
    }

    public function destroy(Article $article): RedirectResponse
    {
        $this->authorizeOwner($article);

        abort_unless($article->isEditableBy(auth()->user()), 403, 'Only drafts or articles needing changes can be deleted.');

        $article->delete();

        return redirect()
            ->route('member.content.index')
            ->with('success', 'Article deleted.');
    }

    private function submitForReview(Article $article): RedirectResponse
    {
        if (! $article->isSeoReady()) {
            return redirect()
                ->route('member.content.edit', $article)
                ->with('error', 'Improve your SEO score before submitting. Still missing: '.implode(', ', $article->failingSeoChecks()).'.');
        }

        $article->update([
            'status' => Article::STATUS_PENDING,
            'submitted_at' => now(),
        ]);

        User::reviewers($article->requiresSuperAdminApproval())
            ->whereKeyNot($article->user_id)
            ->get()
            ->each(fn (User $reviewer) => $reviewer->notify(new ContentSubmitted($article)));

        return redirect()
            ->route('member.content.index')
            ->with('success', $article->requiresSuperAdminApproval()
                ? 'Submitted for review. This topic is reviewed by a super administrator.'
                : 'Submitted for review. An admin will review your article.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Article $article = null): array
    {
        $slugRule = Rule::unique('articles', 'slug');

        if ($article) {
            $slugRule->ignore($article->id);
        }

        return $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'topic' => ['required', Rule::in(array_keys(Article::topics()))],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'body' => ['required', 'string', 'min:100'],
            'cover_image' => ['nullable', 'url', 'max:2048'],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:200'],
            'seo_keywords' => ['nullable', 'string', 'max:255'],
            'canonical_url' => ['nullable', 'url', 'max:2048'],
            'slug' => ['nullable', 'alpha_dash', 'max:180', $slugRule],
            'action' => ['nullable', Rule::in(['save', 'submit'])],
        ]);
    }

    private function authorizeOwner(Article $article): void
    {
        abort_unless($article->user_id === auth()->id(), 403);
    }
}
