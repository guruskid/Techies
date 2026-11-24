@extends('layouts.app')
@section('content')
<section class="relative overflow-hidden aurora text-white py-16">
  <div class="absolute inset-0 tech-grid opacity-25" aria-hidden="true"></div>
  <div class="relative max-w-7xl mx-auto px-4">
    <span class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-white/10 border border-white/20">MWTT Leadership</span>
    <h1 class="mt-5 font-display text-4xl font-bold">Leadership that serves the community.</h1>
    <p class="mt-4 text-slate-300 max-w-2xl">MWTT is a non-profit community led by volunteers who care about connecting and strengthening the technology ecosystem. Leadership is organised around service, transparency and shared ownership.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-16">
  <h2 class="text-2xl font-bold text-[#101E33]">How we are organised</h2>
  <p class="mt-2 text-slate-600 max-w-3xl">Instead of a single central team, MWTT works through coordinators and volunteers at national, chapter and hub level. Each role exists to help members connect, learn and grow.</p>

  <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach ([
      ['National Coordination', 'Guides the overall mission, partnerships and standards for the community.'],
      ['Chapter Coordinators', 'Lead local chapters, organise meetups and represent members in their state or city.'],
      ['Hub Partnerships', 'Work with technology hubs and innovation centres to keep the network open and connected.'],
      ['Programmes & Events', 'Plan learning sessions, workshops, hackathons and community events.'],
      ['Community Management', 'Welcome new members, moderate spaces and keep conversations healthy.'],
      ['Volunteers', 'Members who give their time to support events, content and community activities.'],
    ] as $role)
      <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
        <div class="w-10 h-10 rounded-lg bg-[#FF5C3D]/10 text-[#FF5C3D] flex items-center justify-center font-bold">{{ substr($role[0], 0, 1) }}</div>
        <h3 class="mt-3 font-semibold text-[#101E33]">{{ $role[0] }}</h3>
        <p class="mt-1 text-sm text-slate-500">{{ $role[1] }}</p>
      </div>
    @endforeach
  </div>
</section>

<section class="bg-white py-16">
  <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-2 gap-8 items-center">
    <div>
      <h2 class="text-2xl font-bold text-[#101E33]">Serve, don't gatekeep</h2>
      <p class="mt-3 text-slate-600 leading-relaxed">Leadership at MWTT is about opening doors for others. Coordinators and volunteers are expected to be welcoming, accountable and focused on the needs of the community rather than personal visibility.</p>
      <ul class="mt-4 space-y-2 text-sm text-slate-600">
        <li>• Decisions are made in the open wherever possible.</li>
        <li>• Chapter leads work with members, not above them.</li>
        <li>• Partnerships must benefit the wider ecosystem.</li>
        <li>• Every volunteer role has a clear purpose and handover.</li>
      </ul>
    </div>
    <div class="rounded-2xl bg-[#101E33] text-white p-8">
      <h3 class="text-xl font-bold">Want to lead a chapter?</h3>
      <p class="mt-3 text-slate-300 text-sm">We are always looking for members willing to organise meetups, support hubs and build local communities. If that sounds like you, reach out.</p>
      <a href="{{ route('contact') }}" class="mt-6 inline-block px-5 py-2.5 bg-[#FF5C3D] rounded-lg font-semibold hover:opacity-90 transition">Get in touch</a>
    </div>
  </div>
</section>
@endsection
