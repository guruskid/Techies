@extends('layouts.admin')
@section('title', 'Operations health')
@section('content')
<main class="mx-auto max-w-7xl px-4 py-8 lg:px-6">
  <header class="mb-6">
    <p class="text-xs font-bold uppercase tracking-widest text-[#C23A1F]">System health</p>
    <h1 class="mt-2 font-display text-3xl font-extrabold text-[#101E33]">Operations</h1>
    <p class="mt-2 text-sm text-slate-600">Inspect queued work, failed jobs, payment states, and integration readiness.</p>
  </header>

  @php
    $oldestReady = $oldestQueuedAt ? \Illuminate\Support\Carbon::createFromTimestamp((int) $oldestQueuedAt) : null;
    $oldestAge = $oldestReady?->diffInMinutes(now());
    $schedulerLastRun = $schedulerHeartbeat ? \Illuminate\Support\Carbon::parse($schedulerHeartbeat) : null;
    $schedulerHealthy = $schedulerLastRun?->greaterThan(now()->subMinutes(2)) ?? false;
  @endphp
  <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="System checks">
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Queue</p>
      <p class="mt-2 text-2xl font-bold text-[#101E33]">{{ $queuedJobs }} queued</p>
      <p class="mt-1 text-sm text-slate-600">{{ $readyJobs }} ready for a worker{{ $readyJobs === 1 ? '' : 's' }}</p>
      @if($oldestReady)<p class="mt-2 text-xs {{ $oldestAge > 5 ? 'font-semibold text-red-700' : 'text-slate-500' }}">Oldest ready job: {{ $oldestReady->diffForHumans() }}</p>@endif
    </article>
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Scheduler</p>
      <p class="mt-2 text-lg font-bold {{ $schedulerHealthy ? 'text-green-700' : 'text-amber-800' }}">{{ $schedulerHealthy ? 'Recently active' : 'No recent heartbeat' }}</p>
      <p class="mt-1 text-sm text-slate-600">The cron runner should call <code>schedule:run</code> every minute.</p>
    </article>
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Failed jobs</p>
      <p class="mt-2 text-2xl font-bold text-[#101E33]">{{ number_format($failedJobs->total()) }}</p>
      <p class="mt-1 text-sm text-slate-600">Queue connection: {{ $queueConnection }}</p>
    </article>
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Mail delivery</p>
      <p class="mt-2 text-2xl font-bold text-[#101E33]">{{ $mailMailer }}</p>
      <p class="mt-1 text-sm text-slate-600">Notifications are delivered by the queue worker.</p>
    </article>
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Paystack setup</p>
      <p class="mt-2 text-sm font-semibold {{ $paystackConfigured ? 'text-green-700' : 'text-amber-800' }}">{{ $paystackConfigured ? 'Secret key configured' : 'Secret key missing' }}</p>
      <p class="mt-1 text-sm {{ $paystackWebhookConfigured ? 'text-green-700' : 'text-amber-800' }}">{{ $paystackWebhookConfigured ? 'Webhook signing secret configured' : 'Webhook signing secret missing' }}</p>
    </article>
  </section>

  <section class="mt-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" aria-labelledby="payment-status-heading">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div><h2 id="payment-status-heading" class="font-display text-lg font-bold text-[#101E33]">Payment status summary</h2><p class="mt-1 text-sm text-slate-500">Include this check in daily operations.</p></div>
      <a href="{{ route('admin.payments.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Open payment ledger</a>
    </div>
    <div class="mt-4 flex flex-wrap gap-2">
      @forelse($payments as $status => $count)<span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">{{ \Illuminate\Support\Str::headline($status) }}: {{ $count }}</span>@empty<span class="text-sm text-slate-500">No payment transactions recorded.</span>@endforelse
    </div>
  </section>

  <section class="mt-8" aria-labelledby="failed-jobs-heading">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-3"><div><h2 id="failed-jobs-heading" class="font-display text-xl font-bold text-[#101E33]">Failed queue jobs</h2><p class="mt-1 text-sm text-slate-600">Retry only after the underlying mail or application error is resolved.</p></div></div>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-100 text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th scope="col" class="px-4 py-3">Queue</th><th scope="col" class="px-4 py-3">Failed at</th><th scope="col" class="px-4 py-3">Job ID</th><th scope="col" class="px-4 py-3">Action</th></tr></thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($failedJobs as $job)
            <tr><td class="px-4 py-3">{{ $job->queue }}</td><td class="px-4 py-3">{{ \Illuminate\Support\Carbon::parse($job->failed_at)->diffForHumans() }}</td><td class="px-4 py-3 font-mono text-xs">{{ $job->uuid }}</td><td class="px-4 py-3"><form method="POST" action="{{ route('admin.operations.jobs.retry', $job->uuid) }}">@csrf<button class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D]">Retry job</button></form></td></tr>
          @empty
            <tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">No failed jobs.</td></tr>
          @endforelse
        </tbody>
      </table></div>
      <div class="border-t border-slate-100 p-4">{{ $failedJobs->links() }}</div>
    </div>
  </section>

  <aside class="mt-8 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-950">
    <h2 class="font-semibold">Backup and restore</h2>
    <p class="mt-1">Configure database snapshots with the hosting provider and schedule a restore drill. This application does not create database backups itself.</p>
  </aside>
</main>
@endsection
