@extends('layouts.app')
@section('title', 'Invoices')
@section('header', 'Invoices')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-white">Invoices</h1>
    <a href="{{ route('invoices.create') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded-lg font-medium transition-colors">+ New Invoice</a>
</div>

<form method="GET" action="{{ route('invoices.index') }}" class="flex flex-wrap gap-3 mb-5">
    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
           placeholder="Invoice number..."
           class="bg-slate-800 border border-slate-700 text-slate-200 text-sm rounded-lg px-3 py-2 w-52 focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-slate-500">
    <select name="status" class="bg-slate-800 border border-slate-700 text-slate-200 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <option value="">All Status</option>
        <option value="paid" {{ ($filters['status'] ?? '') === 'paid' ? 'selected' : '' }}>Paid</option>
        <option value="partial" {{ ($filters['status'] ?? '') === 'partial' ? 'selected' : '' }}>Partial</option>
        <option value="credit" {{ ($filters['status'] ?? '') === 'credit' ? 'selected' : '' }}>Credit</option>
        <option value="void" {{ ($filters['status'] ?? '') === 'void' ? 'selected' : '' }}>Void</option>
    </select>
    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded-lg transition-colors">Filter</button>
    <a href="{{ route('invoices.index') }}" class="text-slate-400 hover:text-white text-sm px-3 py-2 rounded-lg transition-colors">Clear</a>
</form>

<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-800/50">
            <tr class="text-xs text-slate-400 uppercase tracking-wider">
                <th class="text-left px-4 py-3">Invoice #</th>
                <th class="text-left px-4 py-3">Date</th>
                <th class="text-left px-4 py-3">Customer</th>
                <th class="text-left px-4 py-3">Payment</th>
                <th class="text-right px-4 py-3">Grand Total</th>
                <th class="text-right px-4 py-3">Paid</th>
                <th class="text-center px-4 py-3">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $invoice)
            @php
                $statusColors = [
                    'paid' => 'bg-emerald-900/50 text-emerald-300',
                    'partial' => 'bg-orange-900/50 text-orange-300',
                    'credit' => 'bg-yellow-900/50 text-yellow-300',
                    'void' => 'bg-red-900/50 text-red-400',
                ];
            @endphp
            <tr class="border-t border-slate-800 hover:bg-slate-800/30 transition-colors">
                <td class="px-4 py-3 font-mono font-medium text-slate-200">
                    <a href="{{ route('invoices.show', $invoice) }}" class="hover:text-indigo-400">{{ $invoice->number }}</a>
                </td>
                <td class="px-4 py-3 text-slate-400">{{ $invoice->invoice_date->format('d M Y') }}</td>
                <td class="px-4 py-3 text-slate-300">{{ $invoice->customer?->name ?? 'Walk-in' }}</td>
                <td class="px-4 py-3 text-slate-400 capitalize">{{ $invoice->payment_mode }}</td>
                <td class="px-4 py-3 text-right font-medium text-slate-200">₹{{ number_format($invoice->grand_total, 2) }}</td>
                <td class="px-4 py-3 text-right text-emerald-400">₹{{ number_format($invoice->paid_amount, 2) }}</td>
                <td class="px-4 py-3 text-center">
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $statusColors[$invoice->status] ?? 'bg-slate-700 text-slate-300' }}">
                        {{ strtoupper($invoice->status) }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('invoices.show', $invoice) }}" class="text-indigo-400 hover:text-indigo-300 text-xs font-medium">View</a>
                        <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank" class="text-slate-400 hover:text-white text-xs font-medium">PDF</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-8 text-center text-slate-600">No invoices found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $invoices->links() }}</div>
@endsection
