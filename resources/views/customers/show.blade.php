@extends('layouts.app')
@section('title', $customer->name . ' — Customer Detail')
@section('header', $customer->name)

@section('content')
@php
  $typeColors = ['retail'=>'bg-blue-100 text-blue-700','wholesale'=>'bg-orange-100 text-orange-700','distributor'=>'bg-green-100 text-green-700','vip'=>'bg-purple-100 text-purple-700'];
  $typeBadge = $typeColors[$customer->customer_type ?? 'retail'] ?? 'bg-slate-100 text-slate-700';
@endphp

{{-- Header Card --}}
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
  <div class="bg-gradient-to-r from-indigo-600 to-purple-600 h-24"></div>
  <div class="px-6 pb-6">
    <div class="flex items-end gap-5 -mt-10 mb-4">
      <div class="w-20 h-20 rounded-2xl border-4 border-white shadow-lg overflow-hidden bg-slate-100 flex items-center justify-center shrink-0">
        @if($customer->photo)
          <img src="{{ Storage::url($customer->photo) }}" class="w-full h-full object-cover">
        @else
          <span class="text-3xl">👤</span>
        @endif
      </div>
      <div class="mb-1">
        <h2 class="text-xl font-bold text-slate-800">{{ $customer->name }}</h2>
        <div class="flex flex-wrap items-center gap-2 mt-1">
          <span class="text-xs font-semibold px-2.5 py-1 rounded-lg {{ $typeBadge }}">
            {{ ucfirst($customer->customer_type ?? 'retail') }}
          </span>
          @if($customer->is_active)
            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-green-100 text-green-700">Active</span>
          @else
            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-red-100 text-red-700">Inactive</span>
          @endif
          @if($customer->billing_cycle !== 'none')
          <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600">{{ ucfirst($customer->billing_cycle) }} billing</span>
          @endif
        </div>
      </div>
      <div class="ml-auto flex gap-2">
        <a href="{{ route('customers.edit', $customer) }}" class="flex items-center gap-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">✏️ Edit</a>
        <form method="POST" action="{{ route('customers.destroy', $customer) }}" onsubmit="return confirm('Delete this customer?')">
          @csrf @method('DELETE')
          <button class="flex items-center gap-1.5 bg-red-50 hover:bg-red-100 text-red-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">🗑️ Delete</button>
        </form>
      </div>
    </div>

    {{-- Contact Info Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      @if($customer->mobile)
      <div class="flex items-center gap-3 bg-slate-50 rounded-xl p-3">
        <span class="text-xl">📞</span>
        <div><div class="text-xs text-slate-500">Mobile</div><div class="text-sm font-semibold text-slate-700">{{ $customer->mobile }}</div></div>
      </div>
      @endif
      @if($customer->email)
      <div class="flex items-center gap-3 bg-slate-50 rounded-xl p-3">
        <span class="text-xl">📧</span>
        <div><div class="text-xs text-slate-500">Email</div><div class="text-sm font-semibold text-slate-700 truncate">{{ $customer->email }}</div></div>
      </div>
      @endif
      @if($customer->gstin)
      <div class="flex items-center gap-3 bg-slate-50 rounded-xl p-3">
        <span class="text-xl">🏛️</span>
        <div><div class="text-xs text-slate-500">GSTIN</div><div class="text-sm font-semibold text-slate-700 font-mono">{{ $customer->gstin }}</div></div>
      </div>
      @endif
      <div class="flex items-center gap-3 bg-slate-50 rounded-xl p-3">
        <span class="text-xl">💳</span>
        <div><div class="text-xs text-slate-500">Credit Limit</div><div class="text-sm font-semibold text-slate-700">₹{{ number_format($customer->credit_limit, 2) }}</div></div>
      </div>
    </div>

    @if($customer->address)
    <div class="mt-4 bg-slate-50 rounded-xl p-3 flex items-start gap-3">
      <span class="text-xl">📍</span>
      <div><div class="text-xs text-slate-500">Address</div><div class="text-sm text-slate-700">{{ $customer->address }}</div></div>
    </div>
    @endif
  </div>
</div>

{{-- Balance Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
  <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
    <div class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Outstanding Balance</div>
    <div class="text-2xl font-bold {{ $customer->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
      ₹{{ number_format(abs($customer->balance), 2) }}
    </div>
    <div class="text-xs text-slate-400 mt-1">{{ $customer->balance > 0 ? 'Amount due' : ($customer->balance < 0 ? 'Credit available' : 'Settled') }}</div>
  </div>
  <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
    <div class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Total Invoices</div>
    <div class="text-2xl font-bold text-slate-800">{{ $invoices->count() }}</div>
    <div class="text-xs text-slate-400 mt-1">Last 10 shown</div>
  </div>
  <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
    <div class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Total Purchased</div>
    <div class="text-2xl font-bold text-slate-800">₹{{ number_format($invoices->sum('grand_total'), 2) }}</div>
    <div class="text-xs text-slate-400 mt-1">All time</div>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

  {{-- Recent Invoices --}}
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
      <h3 class="font-bold text-slate-700">🧾 Recent Invoices</h3>
      <a href="{{ route('invoices.index', ['customer_id' => $customer->id]) }}" class="text-xs text-indigo-600 hover:underline">View All</a>
    </div>
    @forelse($invoices as $inv)
    <div class="flex items-center justify-between px-5 py-3 border-b border-slate-50 hover:bg-slate-50 transition-colors">
      <div>
        <a href="{{ route('invoices.show', $inv) }}" class="text-sm font-semibold text-indigo-600 hover:underline">{{ $inv->number }}</a>
        <div class="text-xs text-slate-400">{{ $inv->invoice_date }}</div>
      </div>
      <div class="text-right">
        <div class="text-sm font-bold text-slate-800">₹{{ number_format($inv->grand_total, 2) }}</div>
        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded {{ $inv->status === 'paid' ? 'bg-green-100 text-green-700' : ($inv->status === 'void' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">{{ strtoupper($inv->status) }}</span>
      </div>
    </div>
    @empty
    <div class="px-5 py-8 text-center text-sm text-slate-400">No invoices yet</div>
    @endforelse
  </div>

  {{-- Ledger + Record Payment --}}
  <div class="space-y-4">
    {{-- Record Payment --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" x-data="{ open: false }">
      <button @click="open=!open" class="w-full flex items-center justify-between px-5 py-4 border-b border-slate-100 hover:bg-slate-50 transition-colors">
        <h3 class="font-bold text-slate-700">💰 Record Payment</h3>
        <svg :class="open?'rotate-180':''" class="w-4 h-4 text-slate-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div x-show="open" x-transition>
        <form method="POST" action="{{ route('customers.record-payment', $customer) }}" class="p-5 space-y-3">
          @csrf
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1">Amount (₹)*</label>
              <input type="number" name="amount" min="0.01" step="0.01" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1">Mode*</label>
              <select name="mode" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                <option value="cash">💵 Cash</option>
                <option value="upi">📱 UPI</option>
                <option value="card">💳 Card</option>
                <option value="bank_transfer">🏦 Bank Transfer</option>
                <option value="other">Other</option>
              </select>
            </div>
          </div>
          <input type="text" name="reference" placeholder="Reference / UTR (optional)" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
          <input type="text" name="note" placeholder="Note (optional)" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
          <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 rounded-lg text-sm transition-colors">✅ Record Payment</button>
        </form>
      </div>
    </div>

    {{-- Ledger --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-100">
        <h3 class="font-bold text-slate-700">📒 Ledger Entries</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
              <th class="px-4 py-2.5 text-left text-xs font-bold text-slate-500">Date</th>
              <th class="px-4 py-2.5 text-left text-xs font-bold text-slate-500">Note</th>
              <th class="px-4 py-2.5 text-right text-xs font-bold text-red-500">Dr</th>
              <th class="px-4 py-2.5 text-right text-xs font-bold text-green-500">Cr</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            @forelse($ledger as $entry)
            <tr class="hover:bg-slate-50">
              <td class="px-4 py-2.5 text-slate-500 whitespace-nowrap text-xs">{{ $entry->created_at->format('d M y') }}</td>
              <td class="px-4 py-2.5 text-slate-700 max-w-36 truncate">{{ $entry->note }}</td>
              <td class="px-4 py-2.5 text-right font-semibold text-red-600">{{ $entry->type === 'debit' ? '₹'.number_format($entry->amount,2) : '' }}</td>
              <td class="px-4 py-2.5 text-right font-semibold text-green-600">{{ $entry->type === 'credit' ? '₹'.number_format($entry->amount,2) : '' }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="px-4 py-6 text-center text-slate-400 text-xs">No ledger entries</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if($ledger->hasPages())
      <div class="px-4 py-3 border-t border-slate-100">{{ $ledger->links() }}</div>
      @endif
    </div>
  </div>
</div>
@endsection
