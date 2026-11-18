<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Your weekly MWTT digest</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#101E33;">
  <div style="max-width:640px;margin:0 auto;padding:24px 16px;">
    <div style="background:#101E33;color:#ffffff;border-radius:16px 16px 0 0;padding:28px 24px;">
      <p style="margin:0;font-size:12px;letter-spacing:2px;text-transform:uppercase;color:#FF5C3D;font-weight:bold;">Meet with the Techies</p>
      <h1 style="margin:8px 0 0;font-size:24px;line-height:1.3;">Your weekly digest</h1>
      <p style="margin:8px 0 0;font-size:14px;color:#cbd5e1;">Hello {{ $member->name }}, here is what is happening in the community.</p>
    </div>

    <div style="background:#ffffff;padding:24px;border-radius:0 0 16px 16px;">
      @if($events->isNotEmpty())
        <h2 style="margin:0 0 12px;font-size:16px;color:#101E33;">Upcoming events</h2>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
          @foreach($events as $event)
            <tr>
              <td style="padding:12px 0;border-top:1px solid #e2e8f0;">
                <a href="{{ route('events.show', $event->slug) }}" style="color:#101E33;font-weight:bold;text-decoration:none;font-size:15px;">{{ $event->title }}</a>
                <div style="margin-top:4px;font-size:13px;color:#64748b;">
                  {{ $event->start_date->format('D, M j, Y') }}@if($event->start_time) at {{ $event->start_time }}@endif
                  @if($event->is_online) · Online @elseif($event->city) · {{ $event->city }} @endif
                </div>
              </td>
            </tr>
          @endforeach
        </table>
      @endif

      @if($articles->isNotEmpty())
        <h2 style="margin:0 0 12px;font-size:16px;color:#101E33;">Latest articles</h2>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
          @foreach($articles as $article)
            <tr>
              <td style="padding:12px 0;border-top:1px solid #e2e8f0;">
                <a href="{{ route('articles.show', $article) }}" style="color:#101E33;font-weight:bold;text-decoration:none;font-size:15px;">{{ $article->title }}</a>
                <div style="margin-top:4px;font-size:13px;color:#64748b;">{{ $article->topicLabel() }}@if($article->author) · {{ $article->author->name }}@endif</div>
              </td>
            </tr>
          @endforeach
        </table>
      @endif

      @if($opportunities->isNotEmpty())
        <h2 style="margin:0 0 12px;font-size:16px;color:#101E33;">Open opportunities</h2>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
          @foreach($opportunities as $opportunity)
            <tr>
              <td style="padding:12px 0;border-top:1px solid #e2e8f0;">
                <a href="{{ route('opportunities.show', $opportunity->slug) }}" style="color:#101E33;font-weight:bold;text-decoration:none;font-size:15px;">{{ $opportunity->title }}</a>
                <div style="margin-top:4px;font-size:13px;color:#64748b;">
                  {{ $opportunity->organization ?: 'MWTT opportunity' }}@if($opportunity->location) · {{ $opportunity->location }}@endif
                </div>
              </td>
            </tr>
          @endforeach
        </table>
      @endif

      <a href="{{ route('home') }}" style="display:inline-block;background:#FF5C3D;color:#ffffff;text-decoration:none;font-weight:bold;font-size:14px;padding:12px 20px;border-radius:10px;">Visit the community</a>

      <p style="margin:24px 0 0;padding-top:16px;border-top:1px solid #e2e8f0;font-size:12px;color:#94a3b8;">
        You are receiving this because you opted in to the weekly digest.
        <a href="{{ route('profile.edit') }}" style="color:#FF5C3D;">Change your email preferences</a>.
      </p>
    </div>
  </div>
</body>
</html>
