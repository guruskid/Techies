<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ config('app.name') }}</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#101E33;">
  <div style="max-width:640px;margin:0 auto;padding:24px 16px;">
    <div style="background:#101E33;color:#ffffff;border-radius:16px 16px 0 0;padding:24px;text-align:center;">
      <p style="margin:0;font-size:12px;letter-spacing:2px;text-transform:uppercase;color:#FF5C3D;font-weight:bold;">Meet with the Techies</p>
    </div>
    <div style="background:#ffffff;padding:28px 24px;border-radius:0 0 16px 16px;font-size:15px;line-height:1.65;">
      {!! $content !!}
    </div>
    <p style="text-align:center;font-size:12px;color:#94a3b8;margin:16px 0 0;">
      Sent from {{ $account?->name ?? config('app.name') }}@if($account?->email) · {{ $account->email }}@endif
    </p>
  </div>
</body>
</html>
