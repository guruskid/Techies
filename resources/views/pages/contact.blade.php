@extends('layouts.app')
@section('content')
<section class="bg-[#101E33] text-white py-16 relative overflow-hidden">
  <div class="absolute inset-0 opacity-20">
    <div class="absolute -top-20 -right-20 w-72 h-72 rounded-full bg-[#FF5C3D] blur-3xl"></div>
  </div>
  <div class="max-w-7xl mx-auto px-4 relative">
    <h1 class="text-4xl font-bold">Contact Us</h1>
    <p class="mt-3 text-slate-300 max-w-2xl">We'd love to hear from you. Reach out to the MWTT team for partnerships, membership, events or any questions.</p>
  </div>
</section>

<div class="max-w-7xl mx-auto px-4 py-12 grid md:grid-cols-3 gap-8">
  <div class="md:col-span-1 space-y-4">
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
      <div class="w-12 h-12 rounded-xl bg-[#FF5C3D]/10 text-[#FF5C3D] flex items-center justify-center font-bold text-xl">@</div>
      <h3 class="mt-3 font-semibold text-[#101E33]">Email</h3>
      <p class="text-sm text-slate-500 mt-1">hello@meettechies.ng</p>
      <a href="mailto:hello@meettechies.ng" class="mt-2 inline-block text-[#FF5C3D] text-sm font-medium hover:underline">Send email →</a>
    </div>
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
      <div class="w-12 h-12 rounded-xl bg-[#FF5C3D]/10 text-[#FF5C3D] flex items-center justify-center font-bold text-xl">#</div>
      <h3 class="mt-3 font-semibold text-[#101E33]">Location</h3>
      <p class="text-sm text-slate-500 mt-1">Nigeria (National)</p>
      <p class="text-xs text-slate-400 mt-1">Chapters across multiple states</p>
    </div>
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
      <div class="w-12 h-12 rounded-xl bg-[#FF5C3D]/10 text-[#FF5C3D] flex items-center justify-center font-bold text-xl">S</div>
      <h3 class="mt-3 font-semibold text-[#101E33]">Social</h3>
      <div class="flex flex-wrap gap-3 mt-2">
        <a href="#" class="text-[#FF5C3D] text-sm font-medium hover:underline">LinkedIn</a>
        <a href="#" class="text-[#FF5C3D] text-sm font-medium hover:underline">X (Twitter)</a>
        <a href="#" class="text-[#FF5C3D] text-sm font-medium hover:underline">Instagram</a>
        <a href="#" class="text-[#FF5C3D] text-sm font-medium hover:underline">Facebook</a>
      </div>
    </div>
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
      <h3 class="font-semibold text-[#101E33] mb-2">Office Hours</h3>
      <p class="text-sm text-slate-500">Mon – Fri: 9:00 AM – 5:00 PM (WAT)</p>
    </div>
  </div>

  <div class="md:col-span-2">
    <div class="bg-white rounded-2xl p-8 border border-slate-100 shadow-sm">
      <h2 class="text-2xl font-bold text-[#101E33] mb-2">Send a Message</h2>
      <p class="text-slate-500 text-sm mb-6">Fill out the form and we'll get back to you as soon as possible.</p>
      <form class="space-y-4">
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Full Name</label>
            <input placeholder="Your name" class="w-full border border-slate-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Email Address</label>
            <input type="email" placeholder="you@example.com" class="w-full border border-slate-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          </div>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Subject</label>
          <input placeholder="What is this about?" class="w-full border border-slate-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Message</label>
          <textarea placeholder="Write your message..." rows="6" class="w-full border border-slate-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]"></textarea>
        </div>
        <button class="w-full px-6 py-3 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Send Message</button>
      </form>
    </div>

    <div class="mt-6 bg-slate-100 rounded-2xl h-48 flex items-center justify-center text-slate-400 text-sm">
      Map placeholder — Google Maps integration
    </div>
  </div>
</div>
@endsection
