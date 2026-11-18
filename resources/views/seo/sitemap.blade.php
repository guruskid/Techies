<?php echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($urls as $entry)
  <url>
    <loc>{{ $entry['url'] }}</loc>
    @if(! empty($entry['lastmod']))
      <lastmod>{{ \Illuminate\Support\Carbon::parse($entry['lastmod'])->toAtomString() }}</lastmod>
    @endif
    <changefreq>{{ $entry['changefreq'] ?? 'monthly' }}</changefreq>
    <priority>{{ $entry['priority'] ?? '0.5' }}</priority>
  </url>
@endforeach
</urlset>
