@extends('layouts.admin')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 pb-12">
  <div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-3">
    <div>
      <h1 class="font-display text-2xl font-extrabold text-[#101E33]">Certificates</h1>
      <p class="mt-1 text-sm text-slate-500">Issue verified certificates for attendance, speaking, volunteering and mentorship.</p>
    </div>
    <a href="{{ route('admin.certificates.create') }}" class="rounded-lg bg-[#FF5C3D] px-4 py-2 text-sm font-semibold text-white hover:bg-[#e94f32]">Issue certificate</a>
  </div>

  <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 text-slate-500">
        <tr><th class="p-3 text-left">Certificate</th><th class="p-3 text-left">Member</th><th class="p-3 text-left">Type</th><th class="p-3 text-left">Issued</th><th class="p-3"></th></tr>
      </thead>
      <tbody>
        @forelse($certificates as $certificate)
          <tr class="border-t border-slate-100">
            <td class="p-3">
              <a href="{{ route('certificates.show', $certificate->certificate_number) }}" class="font-medium text-[#101E33] hover:text-[#C23A1F]">{{ $certificate->title }}</a>
              <div class="text-xs text-slate-500">{{ $certificate->certificate_number }}</div>
            </td>
            <td class="p-3 text-slate-600">{{ $certificate->user?->name }}<div class="text-xs text-slate-500">{{ $certificate->user?->email }}</div></td>
            <td class="p-3 text-slate-600">{{ \App\Models\Certificate::TYPES[$certificate->type] ?? ucfirst($certificate->type) }}</td>
            <td class="p-3 text-slate-600">{{ $certificate->issued_at?->format('M j, Y') }}</td>
            <td class="p-3 text-right">
              <form method="POST" action="{{ route('admin.certificates.destroy', $certificate) }}" onsubmit="return confirm('Delete this certificate?')">
                @csrf @method('DELETE')
                <button class="text-sm text-red-600 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="p-8 text-center text-slate-500">No certificates issued yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-4">{{ $certificates->links() }}</div>
</div>
@endsection