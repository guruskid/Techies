@extends('layouts.app')
@section('title', 'Member benefits · Meet with the Techies')
@section('meta_description', 'Partner offers, tools and resources available to members of Meet with the Techies.')

@section('content')
@include('partials.page-hero', [
  'kicker' => 'Membership value',
  'title' => 'Benefits that make membership useful.',
  'subtitle' => 'Partner offers, tools and resources you can use beyond events and the directory.',
  'actions' => [
    ['label' => 'See membership', 'url' => route('membership.show'), 'style' => 'primary'],
  ],
])

<div class="max-w-7xl mx-auto px-4 py-12 pb-16">
  @if($benefits->isEmpty())
    <div class="mx-auto max-w-2xl rounded-xl border border-slate-200 bg-white p-10 text-center shadow-sm">
      <h2 class="font-display text-xl font-bold text-[#101E33]">Benefits are being prepared</h2>
      <p class="mt-2 text-sm text-slate-500">Check back soon, or contact the team to suggest a partner.</p>
    </div>
  @else
    <div class="space-y-12">
      @foreach($benefits as $category => $items)
        <section aria-labelledby="benefit-{{ \Illuminate\Support\Str::slug($category) }}">
          <div class="mb-5 flex items-end justify-between gap-4">
            <h2 id="benefit-{{ \Illuminate\Support\Str::slug($category) }}" class="font-display text-2xl font-bold text-[#101E33]">{{ $category }}</h2>
            <span class="text-sm text-slate-500">{{ $items->count() }} {{ \Illuminate\Support\Str::plural('offer', $items->count()) }}</span>
          </div>
          <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($items as $benefit)
              <article class="flex h-full flex-col rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                  <div>
                    @if($benefit->provider)<p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $benefit->provider }}</p>@endif
                    <h3 class="mt-1 font-display text-lg font-bold text-[#101E33]">{{ $benefit->title }}</h3>
                  </div>
                  @if($benefit->discount_label)
                    <span class="shrink-0 rounded-full bg-[#FF5C3D]/10 px-2.5 py-1 text-xs font-semibold text-[#C23A1F]">{{ $benefit->discount_label }}</span>
                  @endif
                </div>
                @if($benefit->description)
                  <p class="mt-3 flex-1 text-sm leading-relaxed text-slate-600">{{ $benefit->description }}</p>
                @endif
                @if($benefit->url)
                  <a href="{{ $benefit->url }}" target="_blank" rel="noopener noreferrer" class="mt-5 inline-flex text-sm font-semibold text-[#C23A1F] hover:underline">View offer<span class="sr-only"> (opens in a new tab)</span></a>
                @endif
              </article>
            @endforeach
          </div>
        </section>
      @endforeach
    </div>
  @endif
</div>
@endsection