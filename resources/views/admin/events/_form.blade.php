<style>[x-cloak]{display:none !important}</style>
@php
  $editing = $event->exists;
  $inputClass = 'w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $labelClass = 'block text-sm font-medium text-slate-700 mb-1';
  $hintClass  = 'mt-1 text-xs text-slate-500';
  $focus = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D] focus-visible:ring-offset-2';

  // Inline error under a field
  $err = fn ($name) => $errors->has($name) ? '<p class="mt-1 text-xs text-red-600">'.e($errors->first($name)).'</p>' : '';

  // time columns may be "09:00:00"; <input type="time"> needs "09:00"
  $timeValue = fn ($v) => $v instanceof \Carbon\CarbonInterface ? $v->format('H:i') : substr((string) $v, 0, 5);

  $countries = ['Nigeria','Ghana','Kenya','South Africa','Egypt','Rwanda','Senegal','Ethiopia','Tanzania','Uganda','Cameroon','Ivory Coast','Morocco','Tunisia','Algeria','Other'];

  $locationConfig = [
    'country' => old('country', $event->country ?: 'Nigeria'),
    'state'   => old('state', $event->state) ?? '',
    'city'    => old('city', $event->city) ?? '',
  ];

  $hostConfig = [
    'host'  => old('host', $event->host) ?? '',
    'hosts' => $hosts ?? [],
  ];

  $registrationType = old('registration_type', $event->registration_type ?? 'free');
  $startDate = old('start_date', optional($event->start_date)->format('Y-m-d')) ?? '';
  $currentStatus = old('status', $event->status ?? 'draft');
@endphp

<form method="POST"
      action="{{ $editing ? route('admin.events.update', $event) : route('admin.events.store') }}"
      class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8 space-y-10"
      x-data="{ online: @js((bool) old('is_online', $event->is_online)), paid: @js($registrationType === 'paid'), startDate: @js($startDate) }"
      enctype="multipart/form-data">
  @csrf
  @if($editing)
    @method('PATCH')
  @endif

  {{-- 1. Basics --}}
  <section class="space-y-5" aria-labelledby="sec-basics">
    <h2 id="sec-basics" class="text-lg font-bold text-[#101E33]">Basics</h2>

    <div>
      <label for="title" class="{{ $labelClass }}">Event title</label>
      <input id="title" name="title" value="{{ old('title', $event->title) }}" required class="{{ $inputClass }}">
      {!! $err('title') !!}
    </div>

    <div>
      <label for="description" class="{{ $labelClass }}">Description</label>
      <textarea id="description" name="description" rows="6" class="{{ $inputClass }}">{{ old('description', $event->description) }}</textarea>
      {!! $err('description') !!}
    </div>

    <div>
      <label for="cover_image" class="{{ $labelClass }}">Event banner</label>
      <input id="cover_image" name="cover_image" type="file" accept="image/*"
             class="block w-full text-sm text-slate-600 border border-slate-200 rounded-lg file:mr-4 file:py-2.5 file:px-4 file:border-0 file:bg-slate-100 file:text-slate-700 file:font-medium hover:file:bg-slate-200">
      <p class="{{ $hintClass }}">Upload a wide image (JPG, PNG or WebP). Recommended size: 1600×600 pixels.</p>
      @if($editing && $event->cover_image)
        <div class="mt-3">
          <p class="text-xs text-slate-600 mb-1">Current banner. Upload a new image to replace it.</p>
          <img src="{{ $event->cover_image }}" alt="" class="w-full h-40 object-cover rounded-lg border border-slate-200">
        </div>
      @endif
      {!! $err('cover_image') !!}
    </div>

    {{-- Host: type to search hubs/members, or enter a custom host --}}
    <div x-data="hostPicker(@js($hostConfig))" @click.outside="open = false" @keydown.escape="open = false" class="relative">
      <label for="host" class="{{ $labelClass }}">Event host</label>
      <input id="host" name="host" x-model="host" @focus="open = true" @input="open = true" autocomplete="off"
             role="combobox" aria-autocomplete="list" aria-controls="host-options" :aria-expanded="(open && filtered.length > 0).toString()"
             class="{{ $inputClass }}" placeholder="Search hubs or members, or type a custom host">
      <div x-show="open && filtered.length" x-cloak class="absolute z-20 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg">
        <ul id="host-options" role="listbox" class="max-h-60 overflow-auto py-1">
          <template x-for="item in filtered" :key="item.type + '-' + item.id">
            <li role="option">
              <button type="button" @click="choose(item)" class="block w-full text-left px-3 py-2 text-sm hover:bg-slate-50 focus:bg-slate-50 focus:outline-none" x-text="item.label"></button>
            </li>
          </template>
        </ul>
      </div>
      <p class="{{ $hintClass }}">No match? Just keep what you typed as a custom host.</p>
      {!! $err('host') !!}
    </div>

    <div>
      <label for="key_speakers" class="{{ $labelClass }}">Key speakers</label>
      <textarea id="key_speakers" name="key_speakers" rows="3" class="{{ $inputClass }}" placeholder="One speaker per line">{{ old('key_speakers', $event->key_speakers) }}</textarea>
      {!! $err('key_speakers') !!}
    </div>
  </section>

  {{-- 2. When --}}
  <section class="space-y-5" aria-labelledby="sec-when">
    <h2 id="sec-when" class="text-lg font-bold text-[#101E33]">When</h2>

    <div class="grid sm:grid-cols-2 gap-5">
      <div>
        <label for="start_date" class="{{ $labelClass }}">Start date</label>
        <input id="start_date" name="start_date" type="date" x-model="startDate" required class="{{ $inputClass }}">
        {!! $err('start_date') !!}
      </div>
      <div>
        <label for="end_date" class="{{ $labelClass }}">End date</label>
        <input id="end_date" name="end_date" type="date" :min="startDate" value="{{ old('end_date', optional($event->end_date)->format('Y-m-d')) }}" class="{{ $inputClass }}">
        <p class="{{ $hintClass }}">Leave blank for a one-day event.</p>
        {!! $err('end_date') !!}
      </div>
      <div>
        <label for="start_time" class="{{ $labelClass }}">Start time</label>
        <input id="start_time" name="start_time" type="time" value="{{ old('start_time', $timeValue($event->start_time)) }}" class="{{ $inputClass }}">
        {!! $err('start_time') !!}
      </div>
      <div>
        <label for="end_time" class="{{ $labelClass }}">End time</label>
        <input id="end_time" name="end_time" type="time" value="{{ old('end_time', $timeValue($event->end_time)) }}" class="{{ $inputClass }}">
        {!! $err('end_time') !!}
      </div>
    </div>

    <div>
      <label for="agenda" class="{{ $labelClass }}">Agenda</label>
      <textarea id="agenda" name="agenda" rows="4" class="{{ $inputClass }}" placeholder="Outline the sessions or schedule">{{ old('agenda', $event->agenda) }}</textarea>
      {!! $err('agenda') !!}
    </div>
  </section>

  {{-- 3. Where --}}
  <section class="space-y-5" aria-labelledby="sec-where">
    <h2 id="sec-where" class="text-lg font-bold text-[#101E33]">Where</h2>

    <div class="flex items-center gap-2">
      <input type="checkbox" id="is_online" name="is_online" value="1" x-model="online" class="rounded border-slate-300 text-[#FF5C3D] focus:ring-[#FF5C3D]">
      <label for="is_online" class="text-sm text-slate-700">This is an online event</label>
    </div>

    <div x-show="online" x-cloak>
      <label for="meeting_url" class="{{ $labelClass }}">Meeting link</label>
      <input id="meeting_url" name="meeting_url" type="url" value="{{ old('meeting_url', $event->meeting_url) }}" class="{{ $inputClass }}" placeholder="https://meet.example.com/...">
      {!! $err('meeting_url') !!}
    </div>

    <div x-data="eventLocation(@js($locationConfig))" class="space-y-5">
      <div class="grid sm:grid-cols-3 gap-5">
        <div>
          <label for="country" class="{{ $labelClass }}">Country</label>
          <select id="country" name="country" x-model="country" @change="state = ''; city = ''" class="{{ $inputClass }}">
            @foreach($countries as $c)
              <option value="{{ $c }}">{{ $c }}</option>
            @endforeach
          </select>
          {!! $err('country') !!}
        </div>

        <div>
          <template x-if="isNigeria">
            <div>
              <label for="state" class="{{ $labelClass }}">State</label>
              <select id="state" name="state" x-model="state" @change="city = ''" class="{{ $inputClass }}">
                <option value="">Choose state</option>
                <template x-for="st in states" :key="st">
                  <option :value="st" :selected="st === state" x-text="st"></option>
                </template>
              </select>
            </div>
          </template>
          <template x-if="!isNigeria">
            <div>
              <label for="state" class="{{ $labelClass }}">State / Region</label>
              <input id="state" name="state" x-model="state" class="{{ $inputClass }}">
            </div>
          </template>
          {!! $err('state') !!}
        </div>

        <div>
          <template x-if="isNigeria && cities.length">
            <div>
              <label for="city" class="{{ $labelClass }}">City</label>
              <select id="city" name="city" x-model="city" class="{{ $inputClass }}">
                <option value="">Choose city</option>
                <template x-for="ci in cities" :key="ci">
                  <option :value="ci" :selected="ci === city" x-text="ci"></option>
                </template>
              </select>
            </div>
          </template>
          <template x-if="!(isNigeria && cities.length)">
            <div>
              <label for="city" class="{{ $labelClass }}">City</label>
              <input id="city" name="city" x-model="city" class="{{ $inputClass }}">
            </div>
          </template>
          {!! $err('city') !!}
        </div>
      </div>

      <div>
        <label for="venue" class="{{ $labelClass }}">Venue</label>
        <input id="venue" name="venue" value="{{ old('venue', $event->venue) }}" class="{{ $inputClass }}">
        <p class="{{ $hintClass }}" x-show="online" x-cloak>Optional for online events. Fill it in for hybrid events.</p>
        {!! $err('venue') !!}
      </div>
    </div>
  </section>

  {{-- 4. Registration --}}
  <section class="space-y-5" aria-labelledby="sec-reg">
    <h2 id="sec-reg" class="text-lg font-bold text-[#101E33]">Registration</h2>

    <div class="grid sm:grid-cols-2 gap-5">
      <div>
        <label for="registration_type" class="{{ $labelClass }}">Registration type</label>
        <select id="registration_type" name="registration_type" required @change="paid = $event.target.value === 'paid'" class="{{ $inputClass }}">
          <option value="free" @selected($registrationType === 'free')>Free</option>
          <option value="paid" @selected($registrationType === 'paid')>Paid</option>
        </select>
        {!! $err('registration_type') !!}
      </div>
      <div x-show="paid" x-cloak>
        <label for="fee" class="{{ $labelClass }}">Fee</label>
        <input id="fee" name="fee" type="number" step="0.01" min="0" :disabled="!paid" value="{{ old('fee', $event->fee) }}" class="{{ $inputClass }}">
        {!! $err('fee') !!}
      </div>
      <div x-show="paid" x-cloak>
        <label for="payment_url" class="{{ $labelClass }}">Payment link</label>
        <input id="payment_url" name="payment_url" type="url" :disabled="!paid" value="{{ old('payment_url', $event->payment_url) }}" class="{{ $inputClass }}" placeholder="https://paystack.com/pay/...">
        <p class="{{ $hintClass }}">External checkout or bank-transfer page. Attendees are marked unpaid until an admin confirms payment.</p>
        {!! $err('payment_url') !!}
      </div>
      <div>
        <label for="registration_deadline" class="{{ $labelClass }}">Registration deadline</label>
        <input id="registration_deadline" name="registration_deadline" type="date" value="{{ old('registration_deadline', optional($event->registration_deadline)->format('Y-m-d')) }}" class="{{ $inputClass }}">
        <p class="{{ $hintClass }}">Optional. The last day people can register.</p>
        {!! $err('registration_deadline') !!}
      </div>
    </div>

    <div x-show="paid" x-cloak>
      <label for="payment_instructions" class="{{ $labelClass }}">Payment instructions</label>
      <textarea id="payment_instructions" name="payment_instructions" rows="3" :disabled="!paid" class="{{ $inputClass }}" placeholder="Bank name, account number, reference format, or anything attendees need to pay.">{{ old('payment_instructions', $event->payment_instructions) }}</textarea>
      {!! $err('payment_instructions') !!}
    </div>

    <div>
      <label for="requirements" class="{{ $labelClass }}">Requirements</label>
      <textarea id="requirements" name="requirements" rows="3" class="{{ $inputClass }}" placeholder="What attendees should bring or know">{{ old('requirements', $event->requirements) }}</textarea>
      {!! $err('requirements') !!}
    </div>
  </section>

  {{-- 5. Contact --}}
  <section class="space-y-5" aria-labelledby="sec-contact">
    <h2 id="sec-contact" class="text-lg font-bold text-[#101E33]">Contact</h2>
    <div class="grid sm:grid-cols-2 gap-5">
      <div>
        <label for="contact_email" class="{{ $labelClass }}">Contact email</label>
        <input id="contact_email" name="contact_email" type="email" value="{{ old('contact_email', $event->contact_email) }}" class="{{ $inputClass }}">
        {!! $err('contact_email') !!}
      </div>
      <div>
        <label for="contact_phone" class="{{ $labelClass }}">Contact phone</label>
        <input id="contact_phone" name="contact_phone" type="tel" value="{{ old('contact_phone', $event->contact_phone) }}" class="{{ $inputClass }}">
        {!! $err('contact_phone') !!}
      </div>
    </div>
  </section>

  {{-- 6. Publishing --}}
  <section class="space-y-5" aria-labelledby="sec-publish">
    <h2 id="sec-publish" class="text-lg font-bold text-[#101E33]">Publishing</h2>

    <div class="sm:max-w-xs">
      <label for="status" class="{{ $labelClass }}">Status</label>
      <select id="status" name="status" required class="{{ $inputClass }}">
        <option value="draft" @selected($currentStatus === 'draft')>Draft</option>
        <option value="published" @selected($currentStatus === 'published')>Published</option>
        <option value="cancelled" @selected($currentStatus === 'cancelled')>Cancelled</option>
      </select>
      <p class="{{ $hintClass }}">Keep it as a draft while you work, and set it to Published when it is ready.</p>
      {!! $err('status') !!}
    </div>

    <details class="rounded-lg border border-slate-200 p-4" @if($errors->has('slug')) open @endif>
      <summary class="cursor-pointer text-sm font-medium text-slate-700 {{ $focus }} rounded">Advanced settings</summary>
      <div class="mt-4">
        <label for="slug" class="{{ $labelClass }}">Slug <span class="text-slate-500 font-normal">(optional)</span></label>
        <input id="slug" name="slug" value="{{ old('slug', $event->slug) }}" class="{{ $inputClass }}">
        <p class="{{ $hintClass }}">Leave blank to generate from the event title.</p>
        {!! $err('slug') !!}
      </div>
    </details>
  </section>

  {{-- Sticky action bar --}}
  <div class="sticky bottom-0 -mx-6 sm:-mx-8 -mb-6 sm:-mb-8 px-6 sm:px-8 py-4 bg-white/95 backdrop-blur border-t border-slate-100 rounded-b-2xl flex flex-wrap items-center gap-3">
    <button type="submit" class="px-5 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition {{ $focus }}">{{ $editing ? 'Update event' : 'Create event' }}</button>
    <a href="{{ route('admin.events.index') }}" class="px-5 py-2.5 border border-slate-200 rounded-lg text-slate-700 hover:bg-slate-50 transition {{ $focus }}">Cancel</a>
  </div>
</form>

<script>
function hostPicker(cfg) {
  return {
    open: false,
    host: cfg.host,
    hosts: cfg.hosts,
    get filtered() {
      const q = (this.host || '').toLowerCase().trim();
      if (!q) return this.hosts;
      return this.hosts.filter(item => item.label.toLowerCase().includes(q));
    },
    choose(item) {
      this.host = item.value;
      this.open = false;
    }
  };
}

function eventLocation(cfg) {
  const nigeriaCities = {
    'Abia': ['Umuahia','Aba','Ohafia','Arochukwu','Bende','Isuikwuato'],
    'Adamawa': ['Yola','Mubi','Numan','Jimeta','Ganye','Michika'],
    'Akwa Ibom': ['Uyo','Eket','Ikot Ekpene','Oron','Abak','Ikot Abasi'],
    'Anambra': ['Awka','Onitsha','Nnewi','Ekwulobia','Aguata','Ihiala'],
    'Bauchi': ['Bauchi','Azare','Misau',"Jama'are",'Katagum','Tafawa Balewa'],
    'Bayelsa': ['Yenagoa','Brass','Ogbia','Sagbama','Nembe','Ekeremor'],
    'Benue': ['Makurdi','Gboko','Otukpo','Katsina-Ala','Vandeikya','Zaki Biam'],
    'Borno': ['Maiduguri','Biu','Bama','Dikwa','Monguno','Damaturu'],
    'Cross River': ['Calabar','Ikom','Ogoja','Obudu','Ugep','Akamkpa'],
    'Delta': ['Asaba','Warri','Sapele','Ughelli','Agbor','Ozoro','Effurun'],
    'Ebonyi': ['Abakaliki','Afikpo','Onueke','Ezza','Ikwo'],
    'Edo': ['Benin City','Auchi','Ekpoma','Uromi','Igarra','Igueben'],
    'Ekiti': ['Ado-Ekiti','Ikere','Oye','Ilawe','Efon','Aramoko'],
    'Enugu': ['Enugu','Nsukka','Awgu','Udi','Oji River','Agbani'],
    'FCT (Abuja)': ['Garki','Wuse','Maitama','Asokoro','Kubwa','Gwagwalada','Bwari','Kuje','Abaji'],
    'Gombe': ['Gombe','Kumo','Billiri','Kaltungo','Dukku','Bajoga'],
    'Imo': ['Owerri','Orlu','Okigwe','Oguta','Mbaise','Mbano'],
    'Jigawa': ['Dutse','Hadejia','Gumel','Birnin Kudu','Kazaure','Ringim'],
    'Kaduna': ['Kaduna','Zaria','Kafanchan','Kagoro','Saminaka','Birnin Gwari'],
    'Kano': ['Kano','Nassarawa','Gwale','Fagge','Dala','Tarauni','Kumbotso'],
    'Katsina': ['Katsina','Daura','Funtua','Malumfashi','Mani','Dutsin-Ma'],
    'Kebbi': ['Birnin Kebbi','Argungu','Yauri','Zuru','Jega','Kamba'],
    'Kogi': ['Lokoja','Okene','Kabba','Idah','Ankpa','Dekina','Anyigba'],
    'Kwara': ['Ilorin','Offa','Omu-Aran','Jebba','Patigi','Lafiagi'],
    'Lagos': ['Ikeja','Lekki','Victoria Island','Surulere','Yaba','Ajah','Ikorodu','Badagry','Epe','Ojo','Alimosho','Agege'],
    'Nasarawa': ['Lafia','Keffi','Akwanga','Nasarawa','Karu','Doma'],
    'Niger': ['Minna','Suleja','Kontagora','Bida','Lapai','New Bussa'],
    'Ogun': ['Abeokuta','Ijebu Ode','Sagamu','Ota','Ilaro','Ifo'],
    'Ondo': ['Akure','Ondo','Owo','Okitipupa','Ikare','Ore'],
    'Osun': ['Osogbo','Ile-Ife','Ilesa','Ede','Ikire','Iwo'],
    'Oyo': ['Ibadan','Ogbomosho','Oyo','Iseyin','Saki','Eruwa'],
    'Plateau': ['Jos','Bukuru','Pankshin','Shendam','Langtang','Barkin Ladi'],
    'Rivers': ['Port Harcourt','Obio-Akpor','Bonny','Eleme','Ikwerre','Oyigbo','Okrika','Degema','Ahoada','Omuma','Etche','Tai','Gokana','Khana','Ogba-Egbema-Ndoni','Abua-Odual','Ahoada East','Ahoada West','Akuku-Toru','Andoni','Asari-Toru','Emohua','Opobo-Nkoro'],
    'Sokoto': ['Sokoto','Tambuwal','Wurno','Illela','Gwadabawa','Binji'],
    'Taraba': ['Jalingo','Wukari','Bali','Sardauna','Takum','Gembu'],
    'Yobe': ['Damaturu','Potiskum','Gashua','Nguru','Geidam','Buni Yadi'],
    'Zamfara': ['Gusau','Kaura Namoda','Talata Mafara','Anka','Maru','Bungudu']
  };

  return {
    country: cfg.country,
    state: cfg.state,
    city: cfg.city,
    get isNigeria() { return this.country === 'Nigeria'; },
    get states() { return this.isNigeria ? Object.keys(nigeriaCities) : []; },
    get cities() { return this.isNigeria ? (nigeriaCities[this.state] || []) : []; }
  };
}
</script>