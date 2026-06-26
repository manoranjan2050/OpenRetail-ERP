@extends('layouts.app')
@section('title', 'Audit Log')
@section('header', 'Audit Log')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-white">Activity Log</h1>
</div>

<form method="GET" action="{{ route('audit-log.index') }}" class="flex flex-wrap gap-3 mb-5">
    <select name="log_name" class="bg-slate-800 border border-slate-700 text-slate-200 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <option value="">All Modules</option>
        <option value="invoice" {{ ($filters['log_name'] ?? '') === 'invoice' ? 'selected' : '' }}>Invoice</option>
        <option value="product" {{ ($filters['log_name'] ?? '') === 'product' ? 'selected' : '' }}>Product</option>
        <option value="customer" {{ ($filters['log_name'] ?? '') === 'customer' ? 'selected' : '' }}>Customer</option>
        <option value="settings" {{ ($filters['log_name'] ?? '') === 'settings' ? 'selected' : '' }}>Settings</option>
    </select>
    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}"
           class="bg-slate-800 border border-slate-700 text-slate-200 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}"
           class="bg-slate-800 border border-slate-700 text-slate-200 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded-lg transition-colors">Filter</button>
    <a href="{{ route('audit-log.index') }}" class="text-slate-400 hover:text-white text-sm px-3 py-2 rounded-lg transition-colors">Clear</a>
</form>

<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-800/50">
            <tr class="text-xs text-slate-400 uppercase tracking-wider">
                <th class="text-left px-4 py-3">Time</th>
                <th class="text-left px-4 py-3">Module</th>
                <th class="text-left px-4 py-3">Event</th>
                <th class="text-left px-4 py-3">Description</th>
                <th class="text-left px-4 py-3">User</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr class="border-t border-slate-800 hover:bg-slate-800/20">
                <td class="px-4 py-3 text-slate-500 text-xs whitespace-nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                <td class="px-4 py-3">
                    <span class="text-xs px-2 py-0.5 rounded bg-slate-800 text-slate-400">{{ $log->log_name }}</span>
                </td>
                <td class="px-4 py-3 text-slate-400 text-xs capitalize">{{ $log->event ?? '-' }}</td>
                <td class="px-4 py-3 text-slate-300">{{ $log->description }}</td>
                <td class="px-4 py-3 text-slate-400 text-xs">{{ $log->causer?->name ?? 'System' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-slate-600">No activity logged yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
