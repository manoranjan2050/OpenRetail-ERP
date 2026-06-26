@extends('layouts.app')
@section('title', $customer->name . ' — Khata')
@section('header', $customer->name . ' — Khata / Ledger')

@section('content')
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('customers.index') }}" class="text-slate-400 hover:text-white text-sm">&larr; Back to Customers</a>
    <a href="{{ route('customers.edit', $customer) }}" class="bg-slate-700 hover:bg-slate-600 text-slate-200 text-sm px-4 py-2 rounded-lg transition-colors">Edit Customer</a>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    {{-- Customer info card --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Customer Info</h3>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-slate-400">Name</span>
                <span class="text-slate-200 font-medium">{{ $customer->name }}</span>
            </div>
            @if($customer->mobile)
            <div class="flex justify-between">
                <span class="text-slate-400">Mobile</span>
                <span class="text-slate-200">{{ $customer->mobile }}</span>
            </div>
            @endif
            @if($customer->email)
            <div class="flex justify-between">
                <span class="text-slate-400">Email</span>
                <span class="text-slate-200">{{ $customer->email }}</span>
            </div>
            @endif
            @if($customer->address)
            <div class="flex justify-between gap-2">
                <span class="text-slate-400">Address</span>
                <span class="text-slate-200 text-right">{{ $customer->address }}</span>
            </div>
            @endif
            <div class="flex justify-between">
                <span class="text-slate-400">Credit Limit</span>
                <span class="text-slate-200">{{ $customer->credit_limit ? '₹'.number_format($customer->credit_limit, 2) : 'None' }}</span>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-slate-800">
            <div class="text-xs text-slate-500 mb-1">Current Balance</div>
            <div class="text-2xl font-bold {{ $customer->balance > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                ₹{{ number_format($customer->balance, 2) }}
            </div>
            <div class="text-xs text-slate-600 mt-1">{{ $customer->balance > 0 ? 'Amount owed by customer' : 'No outstanding balance' }}</div>
        </div>
    </div>

    {{-- Record Payment --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Record Payment</h3>
        <form method="POST" action="{{ route('customers.payment', $customer) }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs text-slate-400 mb-1">Amount (₹) <span class="text-red-400">*</span></label>
                <input type="number" name="amount" step="0.01" min="0.01"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('amount') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Mode <span class="text-red-400">*</span></label>
                <select name="mode" class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="cash">Cash</option>
                    <option value="upi">UPI</option>
                    <option value="card">Card</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Reference No</label>
                <input type="text" name="reference"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Note</label>
                <input type="text" name="note"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-medium py-2 rounded-lg transition-colors">
                Record Payment
            </button>
        </form>
    </div>

    {{-- Recent Invoices --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Recent Invoices</h3>
        <div class="space-y-2">
            @forelse($invoices as $inv)
            <div class="flex justify-between items-center text-sm">
                <a href="{{ route('invoices.show', $inv) }}" class="text-indigo-400 hover:text-indigo-300 font-medium">{{ $inv->number }}</a>
                <div class="text-right">
                    <div class="text-slate-300 font-medium">₹{{ number_format($inv->grand_total, 2) }}</div>
                    <div class="text-xs text-slate-500">{{ $inv->invoice_date->format('d M Y') }}</div>
                </div>
            </div>
            @empty
            <p class="text-slate-600 text-xs">No invoices yet.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Ledger Table --}}
<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-800">
        <h3 class="text-sm font-semibold text-slate-300">Ledger Entries</h3>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-slate-800/50">
            <tr class="text-xs text-slate-400 uppercase tracking-wider">
                <th class="text-left px-4 py-3">Date</th>
                <th class="text-left px-4 py-3">Description</th>
                <th class="text-left px-4 py-3">Type</th>
                <th class="text-right px-4 py-3">Debit (₹)</th>
                <th class="text-right px-4 py-3">Credit (₹)</th>
                <th class="text-left px-4 py-3">By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ledger as $entry)
            <tr class="border-t border-slate-800 hover:bg-slate-800/20">
                <td class="px-4 py-3 text-slate-400 text-xs">{{ $entry->created_at->format('d M Y H:i') }}</td>
                <td class="px-4 py-3 text-slate-300">{{ $entry->description ?? '-' }}</td>
                <td class="px-4 py-3">
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $entry->type === 'debit' ? 'bg-rose-900/50 text-rose-300' : 'bg-emerald-900/50 text-emerald-300' }}">
                        {{ ucfirst($entry->type) }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right text-rose-400">{{ $entry->type === 'debit' ? number_format($entry->amount, 2) : '-' }}</td>
                <td class="px-4 py-3 text-right text-emerald-400">{{ $entry->type === 'credit' ? number_format($entry->amount, 2) : '-' }}</td>
                <td class="px-4 py-3 text-slate-500 text-xs">{{ $entry->creator?->name ?? '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-slate-600">No ledger entries yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-3 border-t border-slate-800">
        {{ $ledger->links() }}
    </div>
</div>
@endsection
