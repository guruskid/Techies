<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $query = Article::approved()
            ->with('author')
            ->whereHas('author', fn ($author) => $author->where('status', 'active'));

        if ($request->filled('topic')) {
            $query->where('topic', $request->topic);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        return view('pages.articles', [
            'articles' => $query->orderByDesc('published_at')->paginate(9)->withQueryString(),
            'topics' => Article::topics(),
            'search' => $request->search,
            'activeTopic' => $request->topic,
        ]);
    }

    public function show(Article $article): View
    {
        abort_unless($article->isPublished(), 404);

        abort_unless($article->author?->status === 'active', 404);

        $article->load('author', 'reviewer');
        $article->increment('views');

        $related = Article::approved()
            ->with('author')
            ->where('topic', $article->topic)
            ->whereKeyNot($article->id)
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        return view('pages.article-detail', compact('article', 'related'));
    }
}
