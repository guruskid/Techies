@if(session('success'))
  <div class="max-w-7xl mx-auto px-4 pt-4">
    <div class="p-3 rounded-lg bg-green-50 text-green-700 text-sm border border-green-100">{{ session('success') }}</div>
  </div>
@endif
@if(session('error'))
  <div class="max-w-7xl mx-auto px-4 pt-4">
    <div class="p-3 rounded-lg bg-red-50 text-red-700 text-sm border border-red-100">{{ session('error') }}</div>
  </div>
@endif
