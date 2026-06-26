@extends('layouts.app')
@section('title', 'Customers')
@section('header', 'Customers')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-white">Customers</h1>
    <a href="{{ route('customers.create') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded-lg font-medium transition-colors">+ Add Customer</a>
</div>

<form method="GET" action="{{ route('customers.index') }}" class="flex gap-3 mb-5">
    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
           placeholder="Search name or mobile..."
           class="bg-slate-800 border border-slate-700 text-slate-200 text-sm rounded-lg px-3 py-2 w-72 focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-slate-500">
    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded-lg transition-colors">Search</button>
    <a href="{{ route('customers.index') }}" class="text-slate-400 hover:text-white text-sm px-3 py-2 rounded-lg transition-colors">Clear</a>
</form>

<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-800/50">
            <tr class="text-xs text-slate-400 uppercase tracking-wider">
                <th class="text-left px-4 py-3">Name</th>
                <th class="text-left px-4 py-3">Mobile</th>
                <th class="text-left px-4 py-3">Email</th>
                <th class="text-left px-4 py-3">Billing Cycle</th>
                <th class="text-right px-4 py-3">Balance (₹)</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $customer)
            <tr class="border-t border-slate-800 hover:bg-slate-800/30 transition-colors">
                <td class="px-4 py-3">
                    <a href="{{ route('customers.show', $customer['id']) }}" class="font-medium text-slate-200 hover:text-indigo-400">{{ $customer['name'] }}</a>
                </td>
                <td class="px-4 py-3 text-slate-400">{{ $customer['mobile'] ?? '-' }}</td>
                <td class="px-4 py-3 text-slate-400">{{ $customer['email'] ?? '-' }}</td>
                <td class="px-4 py-3 text-slate-400 capitalize">{{ $customer['billing_cycle'] ?? 'none' }}</td>
                <td class="px-4 py-3 text-right font-medium {{ ($customer['balance'] ?? 0) > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                    {{ number_format($customer['balance'] ?? 0, 2) }}
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('customers.show', $customer['id']) }}" class="text-slate-400 hover:text-white text-xs font-medium">View</a>
                        <a href="{{ route('customers.edit', $customer['id']) }}" class="text-indigo-400 hover:text-indigo-300 text-xs font-medium">Edit</a>
                        <form method="POST" action="{{ route('customers.destroy', $customer['id']) }}" onsubmit="return confirm('Delete {{ addslashes($customer['name']) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-400 text-xs font-medium">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-slate-600">No customers found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $customers->links() }}</div>
@endsection
