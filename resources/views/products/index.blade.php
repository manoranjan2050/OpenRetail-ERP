@extends('layouts.app')
@section('title', 'Products')
@section('header', 'Products')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-white">Products</h1>
    <div class="flex gap-2">
        <a href="{{ route('products.export') }}" class="bg-slate-700 hover:bg-slate-600 text-slate-200 text-sm px-3 py-2 rounded-lg transition-colors">Export CSV</a>
        <a href="{{ route('products.create') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded-lg font-medium transition-colors">+ Add Product</a>
    </div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('products.index') }}" class="flex flex-wrap gap-3 mb-5">
    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
           placeholder="Search name or SKU..."
           class="bg-slate-800 border border-slate-700 text-slate-200 text-sm rounded-lg px-3 py-2 w-60 focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-slate-500">
    <select name="category_id" class="bg-slate-800 border border-slate-700 text-slate-200 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <option value="">All Categories</option>
        @foreach($categories as $cat)
        <option value="{{ $cat->id }}" {{ ($filters['category_id'] ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
        @endforeach
    </select>
    <label class="flex items-center gap-2 text-sm text-slate-400 cursor-pointer">
        <input type="checkbox" name="low_stock" value="1" {{ !empty($filters['low_stock']) ? 'checked' : '' }}
               class="rounded bg-slate-700 border-slate-600 text-indigo-600">
        Low stock only
    </label>
    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded-lg transition-colors">Filter</button>
    <a href="{{ route('products.index') }}" class="text-slate-400 hover:text-white text-sm px-3 py-2 rounded-lg transition-colors">Clear</a>
</form>

<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-800/50">
            <tr class="text-xs text-slate-400 uppercase tracking-wider">
                <th class="text-left px-4 py-3">Name</th>
                <th class="text-left px-4 py-3">SKU</th>
                <th class="text-left px-4 py-3">Category</th>
                <th class="text-right px-4 py-3">Purchase ₹</th>
                <th class="text-right px-4 py-3">Sale ₹</th>
                <th class="text-right px-4 py-3">GST %</th>
                <th class="text-right px-4 py-3">Stock</th>
                <th class="text-center px-4 py-3">Active</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
            <tr class="border-t border-slate-800 hover:bg-slate-800/30 transition-colors">
                <td class="px-4 py-3 font-medium text-slate-200">
                    {{ $product->name }}
                    @if($product->stock_qty <= $product->low_stock_threshold)
                    <span class="ml-1 text-xs bg-amber-800 text-amber-300 px-1.5 py-0.5 rounded">Low</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-slate-400">{{ $product->sku ?? '-' }}</td>
                <td class="px-4 py-3 text-slate-400">{{ $product->category?->name ?? '-' }}</td>
                <td class="px-4 py-3 text-right text-slate-300">{{ number_format($product->purchase_price, 2) }}</td>
                <td class="px-4 py-3 text-right text-emerald-400 font-medium">{{ number_format($product->sale_price, 2) }}</td>
                <td class="px-4 py-3 text-right text-slate-400">{{ $product->gst_rate }}%</td>
                <td class="px-4 py-3 text-right {{ $product->stock_qty <= $product->low_stock_threshold ? 'text-amber-400' : 'text-slate-300' }}">
                    {{ $product->stock_qty }} {{ $product->unit?->symbol }}
                </td>
                <td class="px-4 py-3 text-center">
                    @if($product->is_active)
                    <span class="text-xs bg-emerald-900 text-emerald-400 px-2 py-0.5 rounded-full">Yes</span>
                    @else
                    <span class="text-xs bg-slate-800 text-slate-500 px-2 py-0.5 rounded-full">No</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('products.edit', $product) }}" class="text-indigo-400 hover:text-indigo-300 text-xs font-medium">Edit</a>
                        <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Delete {{ addslashes($product->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-400 text-xs font-medium">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-4 py-8 text-center text-slate-600">No products found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $products->links() }}
</div>
@endsection
