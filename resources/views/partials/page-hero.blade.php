@php
  $kicker = $kicker ?? null;
  $title = $title ?? '';
  $subtitle = $subtitle ?? null;
  $actions = $actions ?? [];
@endphp

<section class="page-hero relative overflow-hidden border-b border-slate-200 bg-white">
  <div class="absolute inset-x-0 top-0 h-40 bg-gradient-to-b from-slate-50 to-transparent" aria-hidden="true"></div>
  <div class="relative max-w-7xl mx-auto px-4 py-9 md:py-12 flex flex-col md:flex-row md:items-end justify-between gap-6">
    <div class="max-w-3xl">
      @if($kicker)<p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#C23A1F]">{{ $kicker }}</p>@endif
      <h1 class="mt-2 font-display text-3xl md:text-4xl font-bold leading-tight text-[#101E33]">{{ $title }}</h1>
      @if($subtitle)<p class="mt-3 max-w-2xl text-base md:text-lg leading-relaxed text-slate-600">{{ $subtitle }}</p>@endif
    </div>

    @if($actions !== [])
      <div class="flex flex-wrap gap-3 shrink-0">
        @foreach($actions as $action)
          @php $primary = ($action['style'] ?? 'primary') === 'primary'; @endphp
          <a href="{{ $action['url'] }}" class="inline-flex items-center justify-center rounded-lg px-5 py-3 text-sm font-semibold transition {{ $primary ? 'bg-[#FF5C3D] text-white hover:bg-[#e94f32] focus-visible:ring-[#FF5C3D]' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 focus-visible:ring-slate-400' }} focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2">
            {{ $action['label'] }}
          </a>
        @endforeach
      </div>
    @endif
  </div>
</section>
