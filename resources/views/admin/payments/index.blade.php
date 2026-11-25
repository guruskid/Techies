@extends('layouts.admin')
@section('title', 'Payments and refunds')
@section('content')
<main class="mx-auto max-w-7xl px-4 py-8 lg:px-6">
  <header class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
      <p class="text-xs font-bold uppercase tracking-widest text-[#C23A1F]">Finance operations</p>
      <h1 class="mt-2 font-display text-3xl font-extrabold text-[#101E33]">Payments and refunds</h1>
      <p class="mt-2 text-sm text-slate-600">Review gateway transactions and follow refunds through Paystack processing.</p>
    </div>
  </header>

  <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
    @foreach(['pending' => 'Pending', 'success' => 'Successful', 'failed' => 'Failed', 'refund_pending' => 'Refund pending', 'refund_needs_attention' => 'Needs attention', 'refunded' => 'Refunded'] as $status => $label)
      <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">{{ $label }}</p>
        <p class="mt-1 text-2xl font-bold text-[#101E33]">{{ $counts[$status] ?? 0 }}</p>
      </div>
    @endforeach
  </div>

  <form method="GET" action="{{ route('admin.payments.index') }}" class="mb-5 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-4">
    <label class="text-sm font-medium text-slate-700">Search
      <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" maxlength="100" placeholder="Reference, member, email" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D]">
    </label>
    <label class="text-sm font-medium text-slate-700">Status
      <select name="status" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D]">
        <option value="">All statuses</option>
        @foreach(['pending', 'success', 'failed', 'refund_pending', 'refund_needs_attention', 'refunded'] as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ \Illuminate\Support\Str::headline($status) }}</option>@endforeach
      </select>
    </label>
    <label class="text-sm font-medium text-slate-700">Purpose
      <select name="purpose" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D]">
        <option value="">All payments</option>
        @foreach(['membership', 'event'] as $purpose)<option value="{{ $purpose }}" @selected(($filters['purpose'] ?? '') === $purpose)>{{ \Illuminate\Support\Str::headline($purpose) }}</option>@endforeach
      </select>
    </label>
    <div class="flex items-end gap-2"><button class="rounded-lg bg-[#101E33] px-4 py-2 font-semibold text-white hover:opacity-90 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D]">Filter</button><a href="{{ route('admin.payments.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 font-semibold text-slate-700 hover:bg-slate-50">Clear</a></div>
  </form>

  <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th scope="col" class="px-4 py-3">Transaction</th><th scope="col" class="px-4 py-3">Member</th><th scope="col" class="px-4 py-3">Purpose</th><th scope="col" class="px-4 py-3">Amount</th><th scope="col" class="px-4 py-3">Status</th><th scope="col" class="px-4 py-3">Date</th><th scope="col" class="px-4 py-3">Action</th></tr></thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($transactions as $transaction)
            <tr class="align-top">
              <td class="whitespace-nowrap px-4 py-4"><span class="font-mono text-xs text-slate-700">{{ $transaction->reference }}</span>@if($transaction->refund_reference)<span class="mt-1 block text-xs text-slate-500">Refund: {{ $transaction->refund_reference }}</span>@endif</td>
              <td class="px-4 py-4"><span class="font-medium text-[#101E33]">{{ $transaction->user?->name ?? 'Account removed' }}</span>@if($transaction->user)<span class="block text-xs text-slate-500">{{ $transaction->user->email }}</span>@endif</td>
              <td class="px-4 py-4">{{ \Illuminate\Support\Str::headline($transaction->purpose) }}</td>
              <td class="whitespace-nowrap px-4 py-4 font-semibold">{{ $transaction->currency }} {{ number_format((float) $transaction->amount, 2) }}</td>
              <td class="px-4 py-4"><span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ \Illuminate\Support\Str::headline($transaction->status) }}</span>@if($transaction->status === 'refund_needs_attention')<span class="mt-1 block max-w-44 text-xs text-amber-800">Action is required in Paystack before refund processing can continue.</span>@endif</td>
              <td class="whitespace-nowrap px-4 py-4 text-slate-600">{{ $transaction->paid_at?->format('M j, Y H:i') ?? $transaction->created_at->format('M j, Y H:i') }}</td>
              <td class="px-4 py-4">
                @if($transaction->provider === 'paystack' && $transaction->status === 'success')
                  <details class="min-w-48">
                    <summary class="cursor-pointer font-semibold text-[#C23A1F] underline underline-offset-2">Start full refund</summary>
                    <form method="POST" action="{{ route('admin.payments.refund', $transaction) }}" class="mt-3 space-y-2">
                      @csrf
                      <label for="refund-reason-{{ $transaction->id }}" class="block text-xs font-medium text-slate-700">Reason (recorded in audit log)</label>
                      <textarea id="refund-reason-{{ $transaction->id }}" name="reason" rows="2" maxlength="500" required class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5C3D]"></textarea>
                      <button class="rounded-lg bg-red-700 px-3 py-2 text-xs font-semibold text-white hover:bg-red-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-700">Submit refund to Paystack</button>
                    </form>
                  </details>
                @else
                  <span class="text-xs text-slate-500">No action available</span>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="px-4 py-12 text-center text-slate-500">No payment transactions match these filters.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="border-t border-slate-100 p-4">{{ $transactions->links() }}</div>
  </div>
</main>
@endsection
