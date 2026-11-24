@php
  $badges = collect([
    'open_to_work' => ['Open to work', 'bg-emerald-50 text-emerald-700 border-emerald-100'],
    'open_to_mentor' => ['Open to mentor', 'bg-blue-50 text-blue-700 border-blue-100'],
    'open_to_collaborate' => ['Open to collaborate', 'bg-slate-100 text-slate-700 border-slate-200'],
    'available_for_speaking' => ['Available for speaking', 'bg-violet-50 text-violet-700 border-violet-100'],
    'hiring' => ['Hiring', 'bg-[#FF5C3D]/10 text-[#C23A1F] border-[#FF5C3D]/20'],
  ])->filter(fn ($_, $key) => $profile?->{$key});
@endphp
@if($badges->isNotEmpty())
  <ul class="flex flex-wrap gap-1.5">
    @foreach($badges as [$label, $classes])
      <li class="rounded-full border px-2 py-0.5 text-[10px] font-semibold {{ $classes }}">{{ $label }}</li>
    @endforeach
  </ul>
@endif
