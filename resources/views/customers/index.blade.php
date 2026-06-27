@extends('layouts.app')
@section('title', 'Customers')
@section('header', 'Customers')

@section('content')
<div class="space-y-5">

  {{-- Header --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
      <h2 class="text-xl font-bold text-slate-800">👥 All Customers</h2>
      <p class="text-sm text-slate-500">{{ $customers->total() }} customer(s) total</p>
    </div>
    <a href="{{ route('customers.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition-colors shadow-sm">➕ Add Customer</a>
  </div>

  {{-- Search --}}
  <form method="GET" class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex gap-3">
    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search by name or mobile..."
      class="flex-1 border border-slate-200 rounded-xl px-3.5 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-colors">
    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors">Search</button>
    @if($filters['search'] ?? '')
    <a href="{{ route('customers.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl text-sm font-semibold transition-colors">Clear</a>
    @endif
  </form>

  {{-- Table --}}
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
          <tr>
            <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wide">Customer</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wide hidden sm:table-cell">Type</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wide hidden md:table-cell">Contact</th>
            <th class="px-4 py-3.5 text-right text-xs font-bold text-slate-500 uppercase tracking-wide">Balance</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wide hidden lg:table-cell">Billing</th>
            <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-500 uppercase tracking-wide">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($customers as $c)
          @php
            $typeColors = ['retail'=>'bg-blue-100 text-blue-700','wholesale'=>'bg-orange-100 text-orange-700','distributor'=>'bg-green-100 text-green-700','vip'=>'bg-purple-100 text-purple-700'];
            $typeBadge = $typeColors[$c['customer_type'] ?? 'retail'] ?? 'bg-slate-100 text-slate-600';
          @endphp
          <tr class="hover:bg-slate-50 transition-colors">
            <td class="px-5 py-3.5">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl overflow-hidden bg-indigo-100 flex items-center justify-center shrink-0">
                  @if($c['photo'])
                    <img src="{{ Storage::url($c['photo']) }}" class="w-full h-full object-cover">
                  @else
                    <span class="text-sm font-bold text-indigo-600">{{ strtoupper(substr($c['name'],0,2)) }}</span>
                  @endif
                </div>
                <div>
                  <a href="{{ route('customers.show', $c['id']) }}" class="font-semibold text-slate-800 hover:text-indigo-600 transition-colors">{{ $c['name'] }}</a>
                  @if($c['gstin'])<div class="text-xs text-slate-400 font-mono">{{ $c['gstin'] }}</div>@endif
                </div>
              </div>
            </td>
            <td class="px-4 py-3.5 hidden sm:table-cell">
              <span class="text-xs font-semibold px-2.5 py-1 rounded-lg {{ $typeBadge }}">{{ ucfirst($c['customer_type'] ?? 'retail') }}</span>
            </td>
            <td class="px-4 py-3.5 hidden md:table-cell">
              <div class="text-slate-700">{{ $c['mobile'] ?? '—' }}</div>
              <div class="text-xs text-slate-400 truncate max-w-36">{{ $c['email'] ?? '' }}</div>
            </td>
            <td class="px-4 py-3.5 text-right">
              <span class="font-bold {{ $c['balance'] > 0 ? 'text-red-600' : ($c['balance'] < 0 ? 'text-green-600' : 'text-slate-400') }}">
                ₹{{ number_format(abs($c['balance']), 2) }}
              </span>
              @if($c['balance'] > 0)<div class="text-[10px] text-red-400">Due</div>
              @elseif($c['balance'] < 0)<div class="text-[10px] text-green-400">Credit</div>
              @endif
            </td>
            <td class="px-4 py-3.5 hidden lg:table-cell">
              <span class="text-xs text-slate-500 bg-slate-100 px-2 py-0.5 rounded">{{ ucfirst($c['billing_cycle']) }}</span>
            </td>
            <td class="px-4 py-3.5">
              <div class="flex items-center justify-center gap-2">
                <a href="{{ route('customers.show', $c['id']) }}" class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="View">👁️</a>
                <a href="{{ route('customers.edit', $c['id']) }}" class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Edit">✏️</a>
                <form method="POST" action="{{ route('customers.destroy', $c['id']) }}" onsubmit="return confirm('Delete {{ addslashes($c['name']) }}?')">
                  @csrf @method('DELETE')
                  <button class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Delete">🗑️</button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr><td colspan="6" class="px-5 py-12 text-center text-slate-400">
            <div class="text-4xl mb-2">👥</div>
            <div>No customers found.</div>
            <a href="{{ route('customers.create') }}" class="mt-2 inline-block text-indigo-600 hover:underline text-sm">Add your first customer</a>
          </td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($customers->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">{{ $customers->links() }}</div>
    @endif
  </div>
</div>
@endsection
