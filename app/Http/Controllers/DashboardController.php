<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $totalProducts = Product::count();
        $totalCustomers = Customer::count();
        $lowStockCount = Product::whereColumn('stock_qty', '<=', 'low_stock_threshold')->count();

        $totalSalesToday = Invoice::whereDate('invoice_date', today())
            ->whereNotIn('status', ['void'])
            ->sum('grand_total');

        $outstandingCredit = LedgerEntry::selectRaw('SUM(CASE WHEN type="debit" THEN amount ELSE -amount END) as balance')
            ->value('balance') ?? 0;

        // Monthly sales last 12 months
        $monthlySales = Invoice::selectRaw('DATE_FORMAT(invoice_date, "%Y-%m") as month, SUM(grand_total) as total')
            ->whereNotIn('status', ['void'])
            ->where('invoice_date', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Top 5 products by quantity sold
        $topProducts = \App\Models\InvoiceItem::selectRaw('product_name, SUM(qty) as total_qty, SUM(line_total) as total_revenue')
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // Sales by payment mode
        $salesByMode = Invoice::selectRaw('payment_mode, SUM(grand_total) as total')
            ->whereNotIn('status', ['void'])
            ->groupBy('payment_mode')
            ->get();

        $lowStockItems = Product::with('unit')
            ->whereColumn('stock_qty', '<=', 'low_stock_threshold')
            ->where('is_active', true)
            ->limit(10)
            ->get(['id', 'name', 'stock_qty', 'low_stock_threshold', 'unit_id']);

        return view('dashboard', [
            'stats' => [
                'totalProducts' => $totalProducts,
                'totalCustomers' => $totalCustomers,
                'lowStockCount' => $lowStockCount,
                'totalSalesToday' => (float) $totalSalesToday,
                'outstandingCredit' => (float) $outstandingCredit,
            ],
            'monthlySales' => $monthlySales,
            'topProducts' => $topProducts,
            'salesByMode' => $salesByMode,
            'lowStockItems' => $lowStockItems,
        ]);
    }
}
