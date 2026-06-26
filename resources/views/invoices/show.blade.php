@extends('layouts.app')
@section('title', 'Invoice ' . $invoice->number)
@section('header', 'Invoice ' . $invoice->number)

@section('content')
@php
    $statusColors = [
        'paid' => 'bg-emerald-900/50 text-emerald-300 border-emerald-700',
        'partial' => 'bg-orange-900/50 text-orange-300 border-orange-700',
        'credit' => 'bg-yellow-900/50 text-yellow-300 border-yellow-700',
        'void' => 'bg-red-900/50 text-red-300 border-red-700',
    ];
@endphp

<div class="flex items-center justify-between mb-6">
    <a href="{{ route('invoices.index') }}" class="text-slate-400 hover:text-white text-sm">&larr; Back to Invoices</a>
    <div class="flex items-center gap-3">
        <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank"
           class="bg-slate-700 hover:bg-slate-600 text-slate-200 text-sm px-4 py-2 rounded-lg transition-colors">
            Download PDF
        </a>
        @if($invoice->status !== 'void')
        <form method="POST" action="{{ route('invoices.void', $invoice) }}" onsubmit="return confirm('Void invoice {{ $invoice->number }}? This cannot be undone.')">
            @csrf
            <button type="submit" class="bg-red-900/50 hover:bg-red-800/50 text-red-400 hover:text-red-300 border border-red-700 text-sm px-4 py-2 rounded-lg transition-colors">
                Void Invoice
            </button>
        </form>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    {{-- Main invoice --}}
    <div class="xl:col-span-2 bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
        {{-- Header --}}
        <div class="px-6 py-5 border-b border-slate-800 flex items-start justify-between">
            <div>
                <div class="text-lg font-bold text-white font-mono">{{ $invoice->number }}</div>
                <div class="text-sm text-slate-400 mt-1">Date: {{ $invoice->invoice_date->format('d M Y') }}</div>
                @if($invoice->due_date)
                <div class="text-sm text-slate-400">Due: {{ $invoice->due_date->format('d M Y') }}</div>
                @endif
            </div>
            <span class="text-xs px-3 py-1 rounded-full border {{ $statusColors[$invoice->status] ?? 'bg-slate-800 text-slate-300 border-slate-700' }}">
                {{ strtoupper($invoice->status) }}
            </span>
        </div>

        {{-- Business + Customer --}}
        <div class="px-6 py-4 grid grid-cols-2 gap-6 border-b border-slate-800">
            <div>
                <div class="text-xs text-slate-500 mb-1">From</div>
                <div class="text-sm font-semibold text-slate-200">{{ $settings['business_name'] ?? config('app.name') }}</div>
                @if(!empty($settings['address'])) <div class="text-xs text-slate-400">{{ $settings['address'] }}</div> @endif
                @if(!empty($settings['gstin'])) <div class="text-xs text-slate-400">GSTIN: {{ $settings['gstin'] }}</div> @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 mb-1">Bill To</div>
                @if($invoice->customer)
                <div class="text-sm font-semibold text-slate-200">{{ $invoice->customer->name }}</div>
                @if($invoice->customer->mobile) <div class="text-xs text-slate-400">{{ $invoice->customer->mobile }}</div> @endif
                @if($invoice->customer->address) <div class="text-xs text-slate-400">{{ $invoice->customer->address }}</div> @endif
                @else
                <div class="text-sm text-slate-400">Walk-in Customer</div>
                @endif
            </div>
        </div>

        {{-- Items --}}
        <table class="w-full text-sm">
            <thead class="bg-slate-800/50">
                <tr class="text-xs text-slate-400 uppercase tracking-wider">
                    <th class="text-left px-4 py-3">#</th>
                    <th class="text-left px-4 py-3">Item</th>
                    <th class="text-right px-4 py-3">Qty</th>
                    <th class="text-right px-4 py-3">Rate ₹</th>
                    <th class="text-right px-4 py-3">GST %</th>
                    <th class="text-right px-4 py-3">Tax ₹</th>
                    <th class="text-right px-4 py-3">Total ₹</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $i => $item)
                <tr class="border-t border-slate-800">
                    <td class="px-4 py-3 text-slate-500 text-xs">{{ $i + 1 }}</td>
                    <td class="px-4 py-3 text-slate-200">{{ $item->product_name }}</td>
                    <td class="px-4 py-3 text-right text-slate-400">{{ $item->qty }}</td>
                    <td class="px-4 py-3 text-right text-slate-300">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="px-4 py-3 text-right text-slate-400">{{ $item->gst_rate }}%</td>
                    <td class="px-4 py-3 text-right text-slate-400">{{ number_format($item->line_tax, 2) }}</td>
                    <td class="px-4 py-3 text-right font-medium text-slate-200">{{ number_format($item->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="border-t-2 border-slate-700">
                <tr>
                    <td colspan="6" class="px-4 py-2 text-right text-slate-400 text-xs">Subtotal</td>
                    <td class="px-4 py-2 text-right text-slate-300">₹{{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="6" class="px-4 py-2 text-right text-slate-400 text-xs">GST</td>
                    <td class="px-4 py-2 text-right text-slate-300">₹{{ number_format($invoice->tax_total, 2) }}</td>
                </tr>
                @if($invoice->discount > 0)
                <tr>
                    <td colspan="6" class="px-4 py-2 text-right text-slate-400 text-xs">Discount</td>
                    <td class="px-4 py-2 text-right text-rose-400">-₹{{ number_format($invoice->discount, 2) }}</td>
                </tr>
                @endif
                <tr class="border-t border-slate-700">
                    <td colspan="6" class="px-4 py-3 text-right font-bold text-slate-200">Grand Total</td>
                    <td class="px-4 py-3 text-right font-bold text-xl text-white">₹{{ number_format($invoice->grand_total, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="6" class="px-4 py-2 text-right text-emerald-400 text-xs">Paid</td>
                    <td class="px-4 py-2 text-right text-emerald-400">₹{{ number_format($invoice->paid_amount, 2) }}</td>
                </tr>
                @if($invoice->grand_total - $invoice->paid_amount > 0)
                <tr>
                    <td colspan="6" class="px-4 py-2 text-right font-semibold text-rose-400 text-sm">Balance Due</td>
                    <td class="px-4 py-2 text-right font-bold text-rose-400">₹{{ number_format($invoice->grand_total - $invoice->paid_amount, 2) }}</td>
                </tr>
                @endif
            </tfoot>
        </table>

        @if($invoice->notes)
        <div class="px-6 py-4 border-t border-slate-800">
            <span class="text-xs text-slate-500 font-medium">Notes: </span>
            <span class="text-sm text-slate-400">{{ $invoice->notes }}</span>
        </div>
        @endif
    </div>

    {{-- Side panel --}}
    <div class="space-y-4">
        {{-- Payment info --}}
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 text-sm space-y-3">
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Payment Details</h3>
            <div class="flex justify-between">
                <span class="text-slate-400">Mode</span>
                <span class="text-slate-200 capitalize">{{ $invoice->payment_mode }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Salesman</span>
                <span class="text-slate-200">{{ $invoice->salesman?->name ?? '-' }}</span>
            </div>
            @if($invoice->store)
            <div class="flex justify-between">
                <span class="text-slate-400">Store</span>
                <span class="text-slate-200">{{ $invoice->store->name }}</span>
            </div>
            @endif
        </div>

        {{-- QR Code --}}
        @if($qrCode)
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 text-center">
            <div class="text-xs text-slate-500 mb-3">Scan to Pay via UPI</div>
            <img src="data:image/png;base64,{{ $qrCode }}" class="w-36 h-36 mx-auto rounded">
            @if(!empty($settings['upi_id']))
            <div class="text-xs text-slate-400 mt-2">{{ $settings['upi_id'] }}</div>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection
