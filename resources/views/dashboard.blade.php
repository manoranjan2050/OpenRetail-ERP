@extends('layouts.app')
@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
{{-- Stat cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 mb-6">
    @php
        $cards = [
            ['label' => 'Products', 'value' => $stats['totalProducts'], 'color' => 'indigo', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
            ['label' => 'Customers', 'value' => $stats['totalCustomers'], 'color' => 'violet', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
            ['label' => 'Low Stock', 'value' => $stats['lowStockCount'], 'color' => 'amber', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
            ['label' => 'Sales Today', 'value' => '₹' . number_format($stats['totalSalesToday'], 2), 'color' => 'emerald', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ['label' => 'Outstanding Credit', 'value' => '₹' . number_format($stats['outstandingCredit'], 2), 'color' => 'rose', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
        ];
    @endphp

    @foreach($cards as $card)
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-lg bg-{{ $card['color'] }}-900/50 flex items-center justify-center">
                <svg class="w-5 h-5 text-{{ $card['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                </svg>
            </div>
            <span class="text-xs text-slate-500 font-medium uppercase tracking-wide">{{ $card['label'] }}</span>
        </div>
        <div class="text-2xl font-bold text-white">{{ $card['value'] }}</div>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    {{-- Monthly Sales Chart --}}
    <div class="xl:col-span-2 bg-slate-900 border border-slate-800 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-slate-300 mb-4">Monthly Sales (Last 12 Months)</h3>
        <canvas id="salesChart" height="100"></canvas>
    </div>

    {{-- Payment mode breakdown --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-slate-300 mb-4">Sales by Payment Mode</h3>
        @php $totalMode = $salesByMode->sum('total') ?: 1; @endphp
        @forelse($salesByMode as $mode)
        <div class="mb-4">
            <div class="flex justify-between text-xs mb-1">
                <span class="text-slate-400 capitalize">{{ $mode->payment_mode ?? 'Unknown' }}</span>
                <span class="text-white font-medium">₹{{ number_format($mode->total, 0) }}</span>
            </div>
            <div class="w-full bg-slate-800 rounded-full h-2">
                <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ round(($mode->total / $totalMode) * 100) }}%"></div>
            </div>
        </div>
        @empty
        <p class="text-slate-600 text-sm">No sales data yet.</p>
        @endforelse
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    {{-- Top Products --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-slate-300 mb-4">Top 5 Products by Quantity Sold</h3>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-xs text-slate-500 border-b border-slate-800">
                    <th class="text-left pb-2">Product</th>
                    <th class="text-right pb-2">Qty</th>
                    <th class="text-right pb-2">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topProducts as $p)
                <tr class="border-b border-slate-800/50">
                    <td class="py-2 text-slate-300">{{ $p->product_name }}</td>
                    <td class="py-2 text-right text-slate-400">{{ number_format($p->total_qty, 2) }}</td>
                    <td class="py-2 text-right text-emerald-400">₹{{ number_format($p->total_revenue, 0) }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="py-4 text-center text-slate-600 text-xs">No data yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Low Stock Alerts --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-slate-300 mb-4">
            Low Stock Alerts
            @if($stats['lowStockCount'] > 0)
            <span class="ml-2 bg-amber-600 text-white text-xs px-2 py-0.5 rounded-full">{{ $stats['lowStockCount'] }}</span>
            @endif
        </h3>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-xs text-slate-500 border-b border-slate-800">
                    <th class="text-left pb-2">Product</th>
                    <th class="text-right pb-2">Stock</th>
                    <th class="text-right pb-2">Min</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lowStockItems as $item)
                <tr class="border-b border-slate-800/50">
                    <td class="py-2">
                        <a href="{{ route('products.edit', $item->id) }}" class="text-slate-300 hover:text-indigo-400">{{ $item->name }}</a>
                    </td>
                    <td class="py-2 text-right text-amber-400 font-medium">{{ $item->stock_qty }}</td>
                    <td class="py-2 text-right text-slate-500">{{ $item->low_stock_threshold }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="py-4 text-center text-slate-600 text-xs">No low stock items</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
const salesData = @json($monthlySales);
const labels = salesData.map(r => r.month);
const values = salesData.map(r => parseFloat(r.total));

const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Sales (₹)',
            data: values,
            backgroundColor: 'rgba(99, 102, 241, 0.7)',
            borderColor: 'rgba(99, 102, 241, 1)',
            borderWidth: 1,
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => '₹' + ctx.parsed.y.toLocaleString('en-IN', { minimumFractionDigits: 2 })
                }
            }
        },
        scales: {
            x: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.05)' } },
            y: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.05)' } }
        }
    }
});
</script>
@endpush
