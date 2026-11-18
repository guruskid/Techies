<?php echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"; ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title>Meet with the Techies — Articles</title>
    <link>{{ route('articles') }}</link>
    <atom:link href="{{ route('feed.articles') }}" rel="self" type="application/rss+xml" />
    <description>Practical articles from the MWTT technology community.</description>
    <language>en</language>
    <lastBuildDate>{{ \Illuminate\Support\Carbon::parse($updatedAt)->toRfc2822String() }}</lastBuildDate>
@foreach($articles as $article)
    <item>
      <title>{{ $article->title }}</title>
      <link>{{ route('articles.show', $article) }}</link>
      <guid isPermaLink="true">{{ route('articles.show', $article) }}</guid>
      <pubDate>{{ ($article->published_at ?? $article->created_at)->toRfc2822String() }}</pubDate>
      <author>{{ $article->author?->email }} ({{ $article->author?->name }})</author>
      <category>{{ $article->topicLabel() }}</category>
      <description>{{ $article->excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $article->body), 300) }}</description>
    </item>
@endforeach
  </channel>
</rss>
