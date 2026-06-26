<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::with(['category', 'unit'])
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('sku', 'like', "%$s%"))
            ->when($request->category_id, fn($q, $c) => $q->where('category_id', $c))
            ->when($request->low_stock, fn($q) => $q->whereColumn('stock_qty', '<=', 'low_stock_threshold'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['search', 'category_id', 'low_stock']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);
        return view('products.form', [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'units' => Unit::orderBy('name')->get(['id', 'name', 'symbol']),
            'gstRates' => [0, 5, 12, 18, 28],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Product::class);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'barcode' => 'nullable|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'hsn_code' => 'nullable|string|max:10',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'gst_rate' => 'required|in:0,5,12,18,28',
            'stock_qty' => 'required|numeric|min:0',
            'low_stock_threshold' => 'required|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'batch' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        if ($data['stock_qty'] > 0) {
            StockMovement::create([
                'product_id' => $product->id,
                'change_qty' => $data['stock_qty'],
                'balance_qty' => $data['stock_qty'],
                'type' => 'purchase',
                'note' => 'Initial stock',
                'created_by' => Auth::id(),
                'created_at' => now(),
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);
        return view('products.form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'units' => Unit::orderBy('name')->get(['id', 'name', 'symbol']),
            'gstRates' => [0, 5, 12, 18, 28],
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'hsn_code' => 'nullable|string|max:10',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'gst_rate' => 'required|in:0,5,12,18,28',
            'low_stock_threshold' => 'required|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'batch' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);
        return redirect()->route('products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }

    public function adjustStock(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);
        $data = $request->validate([
            'change_qty' => 'required|numeric',
            'note' => 'nullable|string|max:255',
        ]);

        $newQty = $product->stock_qty + $data['change_qty'];
        if ($newQty < 0) {
            return back()->withErrors(['change_qty' => 'Insufficient stock.']);
        }

        $product->update(['stock_qty' => $newQty]);
        StockMovement::create([
            'product_id' => $product->id,
            'change_qty' => $data['change_qty'],
            'balance_qty' => $newQty,
            'type' => 'adjustment',
            'note' => $data['note'] ?? 'Manual adjustment',
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);

        return back()->with('success', 'Stock adjusted.');
    }

    public function exportCsv(): StreamedResponse
    {
        $this->authorize('viewAny', Product::class);
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename=products.csv'];
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Name', 'SKU', 'Category', 'Unit', 'Purchase Price', 'Sale Price', 'GST %', 'Stock', 'Low Stock Threshold', 'Active']);
            Product::with(['category', 'unit'])->chunk(200, function ($products) use ($handle) {
                foreach ($products as $p) {
                    fputcsv($handle, [$p->id, $p->name, $p->sku, $p->category?->name, $p->unit?->symbol, $p->purchase_price, $p->sale_price, $p->gst_rate, $p->stock_qty, $p->low_stock_threshold, $p->is_active ? 'Yes' : 'No']);
                }
            });
            fclose($handle);
        }, 'products.csv', $headers);
    }
}
