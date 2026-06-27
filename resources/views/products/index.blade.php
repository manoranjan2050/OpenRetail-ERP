@extends('layouts.app')
@section('title', 'Products')
@section('header', 'Products')

@section('content')
<div class="space-y-5">

  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
      <h2 class="text-xl font-bold text-slate-800">📦 All Products</h2>
      <p class="text-sm text-slate-500">{{ $products->total() }} product(s) total</p>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('products.export') }}" class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-3 py-2 rounded-xl text-sm transition-colors">⬇️ Export CSV</a>
      <a href="{{ route('products.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition-colors shadow-sm">➕ Add Product</a>
    </div>
  </div>

  {{-- Filters --}}
  <form method="GET" action="{{ route('products.index') }}" class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex flex-wrap gap-3">
    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search name or SKU..."
      class="flex-1 min-w-40 border border-slate-200 rounded-xl px-3.5 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-colors">
    <select name="category_id" class="border border-slate-200 rounded-xl px-3.5 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-colors bg-white">
      <option value="">All Categories</option>
      @foreach($categories as $cat)
      <option value="{{ $cat->id }}" {{ ($filters['category_id'] ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
      @endforeach
    </select>
    <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer bg-amber-50 border border-amber-200 px-3 py-2 rounded-xl hover:bg-amber-100 transition-colors">
      <input type="checkbox" name="low_stock" value="1" {{ !empty($filters['low_stock']) ? 'checked' : '' }} class="rounded text-amber-500">
      ⚠️ Low Stock
    </label>
    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors">Filter</button>
    @if(array_filter($filters ?? []))
    <a href="{{ route('products.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2 rounded-xl text-sm font-semibold transition-colors">Clear</a>
    @endif
  </form>

  {{-- Table --}}
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
          <tr>
            <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wide">Product</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wide hidden sm:table-cell">Category</th>
            <th class="px-4 py-3.5 text-right text-xs font-bold text-slate-500 uppercase tracking-wide">Sale Price</th>
            <th class="px-4 py-3.5 text-right text-xs font-bold text-slate-500 uppercase tracking-wide hidden md:table-cell">Stock</th>
            <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-500 uppercase tracking-wide hidden lg:table-cell">GST</th>
            <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-500 uppercase tracking-wide">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($products as $p)
          <tr class="hover:bg-slate-50 transition-colors {{ $p->stock_qty <= $p->low_stock_threshold ? 'bg-amber-50/50' : '' }}">
            <td class="px-5 py-3.5">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-slate-100 overflow-hidden flex items-center justify-center shrink-0">
                  @if($p->image)
                    <img src="{{ Storage::url($p->image) }}" class="w-full h-full object-cover">
                  @else
                    <span>📦</span>
                  @endif
                </div>
                <div>
                  <div class="font-semibold text-slate-800">{{ $p->name }}</div>
                  <div class="text-xs text-slate-400">{{ $p->sku ?? 'No SKU' }} {{ $p->barcode ? '· '.$p->barcode : '' }}</div>
                </div>
              </div>
            </td>
            <td class="px-4 py-3.5 hidden sm:table-cell">
              <span class="text-xs bg-slate-100 text-slate-600 px-2.5 py-1 rounded-lg font-medium">{{ $p->category?->name ?? '—' }}</span>
            </td>
            <td class="px-4 py-3.5 text-right">
              <div class="font-bold text-slate-800">₹{{ number_format($p->sale_price, 2) }}</div>
              <div class="text-xs text-slate-400">Cost: ₹{{ number_format($p->purchase_price, 2) }}</div>
            </td>
            <td class="px-4 py-3.5 text-right hidden md:table-cell">
              <div class="font-bold {{ $p->stock_qty <= $p->low_stock_threshold ? 'text-red-600' : 'text-slate-800' }}">
                {{ $p->stock_qty }} {{ $p->unit?->symbol }}
              </div>
              @if($p->stock_qty <= $p->low_stock_threshold)
              <div class="text-[10px] text-red-400 font-semibold">⚠️ LOW</div>
              @endif
            </td>
            <td class="px-4 py-3.5 text-center hidden lg:table-cell">
              <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded font-medium">{{ $p->gst_rate }}%</span>
            </td>
            <td class="px-4 py-3.5">
              <div class="flex items-center justify-center gap-1">
                <a href="{{ route('products.edit', $p) }}" class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Edit">✏️</a>
                <form method="POST" action="{{ route('products.destroy', $p) }}" onsubmit="return confirm('Delete {{ addslashes($p->name) }}?')">
                  @csrf @method('DELETE')
                  <button class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Delete">🗑️</button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr><td colspan="6" class="px-5 py-12 text-center text-slate-400">
            <div class="text-4xl mb-2">📦</div>
            <div>No products found.</div>
            <a href="{{ route('products.create') }}" class="mt-2 inline-block text-indigo-600 hover:underline text-sm">Add your first product</a>
          </td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($products->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">{{ $products->links() }}</div>
    @endif
  </div>
</div>

{{-- Adjust Stock Modal --}}
<div id="adjustModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
    <h3 class="font-bold text-slate-800 mb-4">⚖️ Adjust Stock</h3>
    <form method="POST" id="adjustForm">
      @csrf
      <div class="space-y-3">
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-1">Change Qty (+ or -)</label>
          <input type="number" name="change_qty" step="0.001" required class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-1">Note</label>
          <input type="text" name="note" placeholder="Reason for adjustment" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
        </div>
        <div class="flex gap-2 pt-1">
          <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 rounded-xl text-sm transition-colors">Adjust</button>
          <button type="button" onclick="document.getElementById('adjustModal').classList.add('hidden')" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-2 rounded-xl text-sm transition-colors">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function openAdjust(id) {
  document.getElementById('adjustForm').action = '/products/' + id + '/adjust-stock';
  document.getElementById('adjustModal').classList.remove('hidden');
}
</script>
@endpush
@endsection
