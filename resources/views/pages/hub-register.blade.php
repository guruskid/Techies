@extends('layouts.app')
@section('content')
<div class="min-h-[70vh] py-12 px-4">
  <div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-0 bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-100">
    <div class="hidden md:flex flex-col justify-between bg-[#101E33] text-white p-10">
      <div>
        <img src="{{ asset('images/logo-light.svg') }}" alt="Meet with the Techies" class="h-9 w-auto">
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
    <div class="p-6 sm:p-10">
      <h1 class="text-2xl font-bold text-[#101E33]">Register Your Hub</h1>
      <p class="mt-1 text-slate-500 text-sm">{{ $canRegisterHub ? 'Tell us about your organisation. Applications are reviewed by the MWTT team.' : 'See the next step for your account below.' }}</p>
      @if(! $canRegisterHub)
        @if($existingHub)
          <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
            <p class="font-semibold text-[#101E33]">Your hub is already registered.</p>
            <p class="mt-1">Check its review status, update details, and manage your team from your hub workspace.</p>
            <a href="{{ route('hub.dashboard') }}" class="mt-3 inline-flex font-semibold text-[#C23A1F] hover:underline">Open hub workspace</a>
          </div>
        @elseif($applicant)
          <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <p class="font-semibold">Activate your membership to apply.</p>
            <p class="mt-1">Hub registration is available to active MWTT members. Once your one-time membership is confirmed, return here to submit your hub.</p>
            <a href="{{ route('membership.show') }}" class="mt-3 inline-flex font-semibold underline">View membership options</a>
          </div>
        @else
          <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
            <p class="font-semibold text-[#101E33]">Start with a member account.</p>
            <p class="mt-1">Hub applications are reviewed and are open to active MWTT members.</p>
            <div class="mt-3 flex flex-wrap gap-3">
              <a href="{{ route('register') }}" class="font-semibold text-[#C23A1F] hover:underline">Create an account</a>
              <a href="{{ route('login') }}" class="font-semibold text-slate-700 hover:underline">Log in</a>
            </div>
          </div>
        @endif
      @endif
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
      @if($canRegisterHub)
      <form method="POST" action="{{ route('hubs.register.store') }}" class="mt-6 space-y-4">
        @csrf
        <div>
          <label for="hub-name" class="block text-xs font-medium text-slate-500 mb-1">Official hub name *</label>
          <input id="hub-name" name="name" value="{{ old('name') }}" autocomplete="organization" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]" required>
        </div>
        <div>
          <label for="hub-description" class="block text-xs font-medium text-slate-500 mb-1">Description</label>
          <textarea id="hub-description" name="description" rows="3" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">{{ old('description') }}</textarea>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label for="hub-year-established" class="block text-xs font-medium text-slate-500 mb-1">Year established</label>
            <input id="hub-year-established" name="year_established" type="number" min="1900" max="2030" value="{{ old('year_established') }}" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          </div>
          <div>
            <label for="hub-organization-type" class="block text-xs font-medium text-slate-500 mb-1">Organization type</label>
            <select id="hub-organization-type" name="organization_type" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
              <option value="">Select</option>
              @foreach(['Tech Hub', 'Innovation Centre', 'Incubator', 'Accelerator', 'University Community', 'Developer Community', 'Other'] as $organizationType)
                <option value="{{ $organizationType }}" @selected(old('organization_type') === $organizationType)>{{ $organizationType }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div>
          <label for="hub-email" class="block text-xs font-medium text-slate-500 mb-1">Official email</label>
          <input id="hub-email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
        </div>
        <div>
          <label for="hub-phone" class="block text-xs font-medium text-slate-500 mb-1">Phone number</label>
          <input id="hub-phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
        </div>
        <div>
          <label for="hub-website" class="block text-xs font-medium text-slate-500 mb-1">Website</label>
          <input id="hub-website" name="website" type="url" value="{{ old('website') }}" autocomplete="url" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label for="hub-contact-person" class="block text-xs font-medium text-slate-500 mb-1">Contact person</label>
            <input id="hub-contact-person" name="contact_person" value="{{ old('contact_person') }}" autocomplete="name" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          </div>
          <div>
            <label for="hub-contact-position" class="block text-xs font-medium text-slate-500 mb-1">Position</label>
            <input id="hub-contact-position" name="contact_position" value="{{ old('contact_position') }}" autocomplete="organization-title" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          </div>
        </div>
        <div>
          <label for="hub-community-size" class="block text-xs font-medium text-slate-500 mb-1">Community size</label>
          <input id="hub-community-size" name="community_size" value="{{ old('community_size') }}" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]" placeholder="e.g. 50–100">
        </div>
        <div class="grid grid-cols-3 gap-3">
          <div>
            <label for="hub-country" class="block text-xs font-medium text-slate-500 mb-1">Country</label>
            <input id="hub-country" name="country" value="{{ old('country', 'Nigeria') }}" autocomplete="country-name" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          </div>
          <div>
            <label for="hub-state" class="block text-xs font-medium text-slate-500 mb-1">State</label>
            <input id="hub-state" name="state" value="{{ old('state') }}" autocomplete="address-level1" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          </div>
          <div>
            <label for="hub-city" class="block text-xs font-medium text-slate-500 mb-1">City</label>
            <input id="hub-city" name="city" value="{{ old('city') }}" autocomplete="address-level2" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
          </div>
        </div>
        <div>
          <label for="hub-address" class="block text-xs font-medium text-slate-500 mb-1">Full address</label>
          <input id="hub-address" name="address" value="{{ old('address') }}" autocomplete="street-address" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
        </div>
        <fieldset class="space-y-3">
          <legend class="block text-xs font-medium text-slate-500">Social media <span class="font-normal">(optional)</span></legend>
          @foreach(['linkedin' => 'LinkedIn', 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'x' => 'X', 'youtube' => 'YouTube'] as $field => $label)
            <div>
              <label for="hub-{{ $field }}" class="mb-1 block text-xs font-medium text-slate-600">{{ $label }}</label>
              <input id="hub-{{ $field }}" name="{{ $field }}" type="url" value="{{ old($field) }}" placeholder="https://" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]">
            </div>
          @endforeach
        </fieldset>
        <div>
          <label for="hub-programs" class="block text-xs font-medium text-slate-500 mb-1">Programs offered</label>
          <textarea id="hub-programs" name="programs_offered" rows="3" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]" placeholder="Training, workshops, incubation, mentorship...">{{ old('programs_offered') }}</textarea>
        </div>
        <button class="w-full px-6 py-3 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Submit Application</button>
      </form>
      @endif
      <p class="mt-6 text-sm text-slate-500 text-center">Already registered? <a href="/hubs" class="text-[#FF5C3D] font-medium hover:underline">View hubs</a></p>
    </div>
  </div>
</div>
@endsection
