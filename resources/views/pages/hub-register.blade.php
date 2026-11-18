@extends('layouts.app')
@section('content')
<div class="min-h-[70vh] py-12 px-4">
  <div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-0 bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-100">
    <div class="hidden md:flex flex-col justify-between bg-[#101E33] text-white p-10">
      <div>
        <div class="text-2xl font-bold">MWTT</div>
        <h2 class="mt-8 text-3xl font-bold leading-tight">Register your technology hub.</h2>
        <p class="mt-4 text-slate-300">Join the MWTT network and connect your tech community with a wider ecosystem.</p>
        <ul class="mt-6 space-y-2 text-sm text-slate-300">
          <li>✓ Dedicated hub profile & public page</li>
          <li>✓ Publish events to the MWTT directory</li>
          <li>✓ Manage your team & community members</li>
          <li>✓ Showcase programs and opportunities</li>
          <li>✓ Get verified by MWTT administrators</li>
        </ul>
      </div>
      <div class="text-sm text-slate-400">© {{ date('Y') }} Meet with the Techies</div>
    </div>
    <div class="p-10 max-h-[85vh] overflow-y-auto">
      <h1 class="text-2xl font-bold text-[#101E33]">Register Your Hub</h1>
      <p class="mt-1 text-slate-500 text-sm">Fill out the form. Your application will be reviewed.</p>
      @guest
        <div class="mt-4 p-3 rounded-lg bg-yellow-50 text-yellow-700 text-sm">
          You need an account before submitting a hub. <a href="{{ route('login') }}" class="font-medium underline">Log in</a> or <a href="{{ route('register') }}" class="font-medium underline">create one</a>.
        </div>
      @endguest
      @if(session('status'))
        <div class="mt-4 p-3 rounded-lg bg-yellow-50 text-yellow-700 text-sm">{{ session('status') }}</div>
      @endif
      @if($errors->any())
        <div class="mt-4 p-3 rounded-lg bg-red-50 text-red-600 text-sm">
          <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
          </ul>
        </div>
      @endif
      <form method="POST" action="{{ route('hubs.register.store') }}" class="mt-6 space-y-4">
        @csrf
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Official Hub Name *</label>
          <input name="name" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]" required>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Description</label>
          <textarea name="description" rows="3" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Year Established</label>
            <input name="year_established" type="number" min="1900" max="2030" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Organization Type</label>
            <select name="organization_type" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
              <option value="">Select</option>
              <option value="Tech Hub">Tech Hub</option>
              <option value="Innovation Centre">Innovation Centre</option>
              <option value="Incubator">Incubator</option>
              <option value="Accelerator">Accelerator</option>
              <option value="University Community">University Community</option>
              <option value="Developer Community">Developer Community</option>
              <option value="Other">Other</option>
            </select>
          </div>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Official Email</label>
          <input name="email" type="email" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Phone Number</label>
          <input name="phone" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Website</label>
          <input name="website" type="url" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Contact Person</label>
            <input name="contact_person" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Position</label>
            <input name="contact_position" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          </div>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Community Size</label>
          <input name="community_size" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]" placeholder="e.g. 50-100">
        </div>
        <div class="grid grid-cols-3 gap-3">
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Country</label>
            <input name="country" value="Nigeria" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">State</label>
            <input name="state" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">City</label>
            <input name="city" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          </div>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Full Address</label>
          <input name="address" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
        </div>
        <div class="space-y-3">
          <label class="block text-xs font-medium text-slate-500">Social Media (optional)</label>
          <input name="linkedin" type="url" placeholder="LinkedIn URL" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          <input name="facebook" type="url" placeholder="Facebook URL" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          <input name="instagram" type="url" placeholder="Instagram URL" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          <input name="x" type="url" placeholder="X (Twitter) URL" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          <input name="youtube" type="url" placeholder="YouTube URL" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Programs Offered</label>
          <textarea name="programs_offered" rows="3" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]" placeholder="Training, workshops, incubation, mentorship..."></textarea>
        </div>
        <button class="w-full px-6 py-3 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Submit Application</button>
      </form>
      <p class="mt-6 text-sm text-slate-500 text-center">Already registered? <a href="/hubs" class="text-[#FF5C3D] font-medium hover:underline">View hubs</a></p>
    </div>
  </div>
</div>
@endsection
