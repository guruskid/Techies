<?php echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"; ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title>Meet with the Techies — Events</title>
    <link>{{ route('events') }}</link>
    <atom:link href="{{ route('feed.events') }}" rel="self" type="application/rss+xml" />
    <description>Upcoming events from the MWTT technology community.</description>
    <language>en</language>
    <lastBuildDate>{{ \Illuminate\Support\Carbon::parse($updatedAt)->toRfc2822String() }}</lastBuildDate>
@foreach($events as $event)
    <item>
      <title>{{ $event->title }}</title>
      <link>{{ route('events.show', $event->slug) }}</link>
      <guid isPermaLink="true">{{ route('events.show', $event->slug) }}</guid>
      <pubDate>{{ $event->created_at->toRfc2822String() }}</pubDate>
      <category>{{ $event->is_online ? 'Online' : ($event->city ?: 'In person') }}</category>
      <description>{{ \Illuminate\Support\Str::limit(strip_tags((string) $event->description), 300) }}</description>
    </item>
@endforeach
  </channel>
</rss>
