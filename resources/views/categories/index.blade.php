@extends('layouts.app')
@section('title', 'Categories')
@section('header', 'Categories')

@section('content')
<div class="space-y-5">

  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
      <h2 class="text-xl font-bold text-slate-800">🗂️ Product Categories</h2>
      <p class="text-sm text-slate-500">{{ $categories->count() }} categories total</p>
    </div>
    <a href="{{ route('categories.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition-colors shadow-sm">➕ Add Category</a>
  </div>

  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden max-w-3xl">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
          <tr>
            <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wide">Name</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wide hidden sm:table-cell">Description</th>
            <th class="px-4 py-3.5 text-right text-xs font-bold text-slate-500 uppercase tracking-wide">Products</th>
            <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-500 uppercase tracking-wide">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($categories as $cat)
          <tr class="hover:bg-slate-50 transition-colors">
            <td class="px-5 py-3.5">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center shrink-0">
                  <span class="text-sm font-bold text-indigo-600">{{ strtoupper(substr($cat->name,0,1)) }}</span>
                </div>
                <div>
                  <div class="font-semibold text-slate-800">{{ $cat->name }}</div>
                  <div class="text-xs text-slate-400 font-mono">{{ $cat->slug }}</div>
                </div>
              </div>
            </td>
            <td class="px-4 py-3.5 hidden sm:table-cell text-slate-500 max-w-48 truncate">{{ $cat->description ?? '—' }}</td>
            <td class="px-4 py-3.5 text-right">
              <span class="font-bold text-slate-700">{{ $cat->products_count ?? $cat->products()->count() }}</span>
            </td>
            <td class="px-4 py-3.5">
              <div class="flex items-center justify-center gap-2">
                <a href="{{ route('categories.edit', $cat) }}" class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Edit">✏️</a>
                <form method="POST" action="{{ route('categories.destroy', $cat) }}" onsubmit="return confirm('Delete category {{ addslashes($cat->name) }}?')">
                  @csrf @method('DELETE')
                  <button class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Delete">🗑️</button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr><td colspan="4" class="px-5 py-12 text-center text-slate-400">
            <div class="text-4xl mb-2">🗂️</div>
            <div>No categories yet.</div>
            <a href="{{ route('categories.create') }}" class="mt-2 inline-block text-indigo-600 hover:underline text-sm">Create the first category</a>
          </td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
