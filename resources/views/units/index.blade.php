@extends('layouts.app')
@section('title', 'Units')
@section('header', 'Units of Measure')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-white">Units of Measure</h1>
    <a href="{{ route('units.create') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded-lg font-medium transition-colors">+ Add Unit</a>
</div>

<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden max-w-lg">
    <table class="w-full text-sm">
        <thead class="bg-slate-800/50">
            <tr class="text-xs text-slate-400 uppercase tracking-wider">
                <th class="text-left px-4 py-3">Name</th>
                <th class="text-left px-4 py-3">Symbol</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($units as $unit)
            <tr class="border-t border-slate-800 hover:bg-slate-800/30">
                <td class="px-4 py-3 font-medium text-slate-200">{{ $unit->name }}</td>
                <td class="px-4 py-3 text-slate-400 font-mono">{{ $unit->symbol }}</td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('units.edit', $unit) }}" class="text-indigo-400 hover:text-indigo-300 text-xs font-medium">Edit</a>
                        <form method="POST" action="{{ route('units.destroy', $unit) }}" onsubmit="return confirm('Delete {{ addslashes($unit->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-400 text-xs font-medium">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="3" class="px-4 py-8 text-center text-slate-600">No units yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
