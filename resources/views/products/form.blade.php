@extends('layouts.app')
@section('title', isset($product) ? 'Edit Product' : 'Add Product')
@section('header', isset($product) ? 'Edit Product' : 'Add Product')

@section('content')
<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('products.index') }}" class="text-slate-400 hover:text-white text-sm">&larr; Back to Products</a>
        <span class="text-slate-700">/</span>
        <h1 class="text-xl font-bold text-white">{{ isset($product) ? 'Edit: '.$product->name : 'Add New Product' }}</h1>
    </div>

    <form method="POST"
          action="{{ isset($product) ? route('products.update', $product) : route('products.store') }}"
          enctype="multipart/form-data"
          class="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-5">
        @csrf
        @if(isset($product)) @method('PUT') @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {{-- Name --}}
            <div class="sm:col-span-2">
                <label class="block text-xs text-slate-400 mb-1 font-medium">Product Name <span class="text-red-400">*</span></label>
                <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}"
                       class="w-full bg-slate-800 border {{ $errors->has('name') ? 'border-red-500' : 'border-slate-700' }} text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- SKU --}}
            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">SKU</label>
                <input type="text" name="sku" value="{{ old('sku', $product->sku ?? '') }}"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('sku') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Barcode --}}
            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">Barcode</label>
                <input type="text" name="barcode" value="{{ old('barcode', $product->barcode ?? '') }}"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            {{-- Category --}}
            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">Category</label>
                <select name="category_id" class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- None --</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Unit --}}
            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">Unit</label>
                <select name="unit_id" class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- None --</option>
                    @foreach($units as $unit)
                    <option value="{{ $unit->id }}" {{ old('unit_id', $product->unit_id ?? '') == $unit->id ? 'selected' : '' }}>{{ $unit->name }} ({{ $unit->symbol }})</option>
                    @endforeach
                </select>
            </div>

            {{-- HSN Code --}}
            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">HSN Code</label>
                <input type="text" name="hsn_code" value="{{ old('hsn_code', $product->hsn_code ?? '') }}"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            {{-- Purchase Price --}}
            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">Purchase Price (₹) <span class="text-red-400">*</span></label>
                <input type="number" name="purchase_price" step="0.01" min="0"
                       value="{{ old('purchase_price', $product->purchase_price ?? '') }}"
                       class="w-full bg-slate-800 border {{ $errors->has('purchase_price') ? 'border-red-500' : 'border-slate-700' }} text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('purchase_price') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Sale Price --}}
            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">Sale Price (₹) <span class="text-red-400">*</span></label>
                <input type="number" name="sale_price" step="0.01" min="0"
                       value="{{ old('sale_price', $product->sale_price ?? '') }}"
                       class="w-full bg-slate-800 border {{ $errors->has('sale_price') ? 'border-red-500' : 'border-slate-700' }} text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('sale_price') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- GST Rate --}}
            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">GST Rate <span class="text-red-400">*</span></label>
                <select name="gst_rate" class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($gstRates as $rate)
                    <option value="{{ $rate }}" {{ old('gst_rate', $product->gst_rate ?? 0) == $rate ? 'selected' : '' }}>{{ $rate }}%</option>
                    @endforeach
                </select>
            </div>

            @if(!isset($product))
            {{-- Initial Stock --}}
            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">Initial Stock Qty <span class="text-red-400">*</span></label>
                <input type="number" name="stock_qty" step="0.001" min="0"
                       value="{{ old('stock_qty', 0) }}"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            @endif

            {{-- Low Stock Threshold --}}
            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">Low Stock Threshold <span class="text-red-400">*</span></label>
                <input type="number" name="low_stock_threshold" step="0.001" min="0"
                       value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 5) }}"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            {{-- Batch --}}
            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">Batch No</label>
                <input type="text" name="batch" value="{{ old('batch', $product->batch ?? '') }}"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            {{-- Expiry Date --}}
            <div>
                <label class="block text-xs text-slate-400 mb-1 font-medium">Expiry Date</label>
                <input type="date" name="expiry_date" value="{{ old('expiry_date', isset($product->expiry_date) ? $product->expiry_date->format('Y-m-d') : '') }}"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            {{-- Image --}}
            <div class="sm:col-span-2">
                <label class="block text-xs text-slate-400 mb-1 font-medium">Product Image</label>
                <input type="file" name="image" accept="image/*"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-400 rounded-lg px-3 py-2 text-sm file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:bg-indigo-600 file:text-white">
                @if(isset($product) && $product->image)
                <p class="text-xs text-slate-500 mt-1">Current: <span class="text-slate-400">{{ $product->image }}</span></p>
                @endif
            </div>
        </div>

        <div class="flex gap-3 pt-2 border-t border-slate-800">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                {{ isset($product) ? 'Update Product' : 'Create Product' }}
            </button>
            <a href="{{ route('products.index') }}" class="bg-slate-700 hover:bg-slate-600 text-slate-200 text-sm px-5 py-2 rounded-lg transition-colors">Cancel</a>
        </div>
    </form>

    @if(isset($product))
    {{-- Stock Adjustment --}}
    <div class="mt-6 bg-slate-900 border border-slate-800 rounded-xl p-6">
        <h3 class="text-sm font-semibold text-slate-300 mb-4">Adjust Stock (Current: {{ $product->stock_qty }} {{ $product->unit?->symbol }})</h3>
        <form method="POST" action="{{ route('products.adjust-stock', $product) }}" class="flex flex-wrap gap-3 items-end">
            @csrf
            <div>
                <label class="block text-xs text-slate-400 mb-1">Change Qty (+ or -)</label>
                <input type="number" name="change_qty" step="0.001" placeholder="e.g. -5 or +10"
                       class="bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm w-40 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Note</label>
                <input type="text" name="note" placeholder="Reason..."
                       class="bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm w-56 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <button type="submit" class="bg-amber-600 hover:bg-amber-500 text-white text-sm px-4 py-2 rounded-lg transition-colors">Adjust</button>
        </form>
        @error('change_qty') <p class="text-red-400 text-xs mt-2">{{ $message }}</p> @enderror
    </div>
    @endif
</div>
@endsection
