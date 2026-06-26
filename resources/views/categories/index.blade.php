@extends('layouts.app')
@section('title', 'Categories')
@section('header', 'Categories')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-white">Product Categories</h1>
    <a href="{{ route('categories.create') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded-lg font-medium transition-colors">+ Add Category</a>
</div>

<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden max-w-2xl">
    <table class="w-full text-sm">
        <thead class="bg-slate-800/50">
            <tr class="text-xs text-slate-400 uppercase tracking-wider">
                <th class="text-left px-4 py-3">Name</th>
                <th class="text-left px-4 py-3">Description</th>
                <th class="text-right px-4 py-3">Products</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $category)
            <tr class="border-t border-slate-800 hover:bg-slate-800/30">
                <td class="px-4 py-3 font-medium text-slate-200">{{ $category->name }}</td>
                <td class="px-4 py-3 text-slate-400">{{ $category->description ?? '-' }}</td>
                <td class="px-4 py-3 text-right text-slate-400">{{ $category->products_count }}</td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('categories.edit', $category) }}" class="text-indigo-400 hover:text-indigo-300 text-xs font-medium">Edit</a>
                        <form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('Delete {{ addslashes($category->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-400 text-xs font-medium">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="px-4 py-8 text-center text-slate-600">No categories yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
