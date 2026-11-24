@extends('layouts.app')
@section('title', $certificate->title.' · Certificate')
@section('meta_description', 'Verify a certificate issued by Meet with the Techies.')
@section('content')
<div class="mx-auto max-w-4xl px-4 py-10 pb-16">
  <div class="mb-5 flex flex-wrap items-center justify-between gap-3 print:hidden">
    <a href="{{ route('passport.show') }}" class="text-sm font-medium text-[#C23A1F] hover:underline">Back to passport</a>
    <button type="button" onclick="window.print()" class="rounded-lg bg-[#101E33] px-4 py-2 text-sm font-semibold text-white">Print certificate</button>
  </div>

  <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm print:border-0 print:shadow-none">
    <div class="border-b border-slate-100 bg-[#101E33] px-8 py-8 text-white">
      <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#FF8A73]">Certificate of participation</p>
      <h1 class="mt-2 font-display text-3xl font-extrabold">{{ $certificate->title }}</h1>
    </div>

    <div class="p-8 sm:p-12">
      <p class="text-sm text-slate-500">This certifies that</p>
      <p class="mt-2 font-display text-4xl font-extrabold text-[#101E33]">{{ $certificate->user?->name }}</p>
      <p class="mt-4 max-w-2xl text-slate-600">
        {{ $certificate->description ?: 'has participated in and contributed to the Meet with the Techies community.' }}
      </p>

      <dl class="mt-8 grid sm:grid-cols-2 gap-5 text-sm">
        <div><dt class="text-slate-500">Type</dt><dd class="mt-1 font-semibold text-[#101E33]">{{ \App\Models\Certificate::TYPES[$certificate->type] ?? ucfirst($certificate->type) }}</dd></div>
        <div><dt class="text-slate-500">Issued</dt><dd class="mt-1 font-semibold text-[#101E33]">{{ $certificate->issued_at?->format('M j, Y') }}</dd></div>
        @if($certificate->event)
          <div><dt class="text-slate-500">Event</dt><dd class="mt-1 font-semibold text-[#101E33]">{{ $certificate->event->title }}</dd></div>
        @endif
        <div><dt class="text-slate-500">Certificate number</dt><dd class="mt-1 font-mono font-semibold text-[#101E33]">{{ $certificate->certificate_number }}</dd></div>
      </dl>

      <div class="mt-10 flex flex-wrap items-end justify-between gap-4 border-t border-slate-100 pt-6">
        <div>
          <p class="font-display text-lg font-bold text-[#101E33]">Meet with the Techies</p>
          <p class="text-xs text-slate-500">Verified by {{ $certificate->issuer?->name ?: 'the MWTT team' }}</p>
        </div>
        <p class="text-xs text-slate-500">Verify at {{ route('certificates.show', $certificate->certificate_number) }}</p>
      </div>
    </div>
  </article>
</div>
@endsection