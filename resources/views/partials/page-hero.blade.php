@php
  $kicker = $kicker ?? null;
  $title = $title ?? '';
  $subtitle = $subtitle ?? null;
  $actions = $actions ?? [];
@endphp

<section class="relative overflow-hidden aurora text-white">
  <div class="absolute inset-0 tech-grid opacity-35" aria-hidden="true"></div>
  <div class="absolute -top-24 right-0 h-72 w-72 rounded-full bg-[#FF5C3D]/15 blur-3xl" aria-hidden="true"></div>

  <div class="relative max-w-7xl mx-auto px-4 py-12 md:py-16 flex flex-col md:flex-row md:items-end justify-between gap-6">
    <div class="max-w-3xl">
      @if($kicker)<p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#FF8A73]">{{ $kicker }}</p>@endif
      <h1 class="mt-2 font-display text-3xl md:text-5xl font-bold leading-tight">{{ $title }}</h1>
      @if($subtitle)<p class="mt-3 text-lg leading-relaxed text-slate-300">{{ $subtitle }}</p>@endif
    </div>

    @if($actions !== [])
      <div class="flex flex-wrap gap-3 shrink-0">
        @foreach($actions as $action)
          @php $primary = ($action['style'] ?? 'primary') === 'primary'; @endphp
          <a href="{{ $action['url'] }}" class="inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold transition {{ $primary ? 'btn-glow bg-[#FF5C3D] text-white hover:opacity-90 focus-visible:ring-offset-[#080F1D]' : 'border border-white/20 bg-white/5 text-white hover:bg-white/10 focus-visible:ring-offset-[#080F1D]' }} focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
            {{ $action['label'] }}
          </a>
        @endforeach
      </div>
    @endif
  </div>
</section>
