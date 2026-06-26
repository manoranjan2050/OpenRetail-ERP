<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Store;
use App\Services\InvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Invoice::class);
        $invoices = Invoice::with(['customer', 'salesman'])
            ->when($request->search, fn($q, $s) => $q->where('number', 'like', "%$s%"))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->customer_id, fn($q, $c) => $q->where('customer_id', $c))
            ->latest('invoice_date')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
            'filters' => $request->only(['search', 'status', 'customer_id']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Invoice::class);
        return Inertia::render('Invoices/Create', [
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(['id', 'name', 'mobile', 'credit_limit', 'billing_cycle']),
            'products' => Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sale_price', 'gst_rate', 'stock_qty', 'unit_id']),
            'stores' => Store::where('is_active', true)->get(['id', 'name']),
            'settings' => BusinessSetting::instance()->only(['upi_id', 'payee_name', 'show_qr_on_invoice', 'currency']),
        ]);
    }

    public function store(Request $request, InvoiceService $service): RedirectResponse
    {
        $this->authorize('create', Invoice::class);
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'payment_mode' => 'required|in:cash,upi,card,credit,mixed',
            'discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'store_id' => 'nullable|exists:stores,id',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $invoice = $service->create($request->all());
        } catch (\RuntimeException $e) {
            return back()->withErrors(['credit' => $e->getMessage()]);
        }

        return redirect()->route('invoices.show', $invoice)->with('success', "Invoice {$invoice->number} created.");
    }

    public function show(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);
        $invoice->load(['items.product', 'customer', 'salesman', 'store']);
        $settings = BusinessSetting::instance();

        $qrCode = null;
        if ($settings->show_qr_on_invoice && $settings->upi_id) {
            $upiString = "upi://pay?pa={$settings->upi_id}&pn={$settings->payee_name}&am={$invoice->grand_total}&tn={$invoice->number}&cu=INR";
            $qrCode = base64_encode(QrCode::format('png')->size(150)->generate($upiString));
        }

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice,
            'settings' => $settings->only(['business_name', 'address', 'gstin', 'upi_id', 'payee_name', 'show_qr_on_invoice']),
            'qrCode' => $qrCode,
        ]);
    }

    public function pdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        $invoice->load(['items.product', 'customer', 'salesman', 'store']);
        $settings = BusinessSetting::instance();

        $qrCode = null;
        if ($settings->show_qr_on_invoice && $settings->upi_id) {
            $upiString = "upi://pay?pa={$settings->upi_id}&pn={$settings->payee_name}&am={$invoice->grand_total}&tn={$invoice->number}&cu=INR";
            $qrCode = base64_encode(QrCode::format('png')->size(150)->generate($upiString));
        }

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice', 'settings', 'qrCode'));
        return $pdf->stream("invoice-{$invoice->number}.pdf");
    }

    public function void(Invoice $invoice): RedirectResponse
    {
        $this->authorize('void', $invoice);
        if ($invoice->status === 'void') {
            return back()->withErrors(['error' => 'Already voided.']);
        }
        $invoice->update(['status' => 'void']);
        activity('invoice')->performedOn($invoice)->log("Invoice {$invoice->number} voided");
        return redirect()->route('invoices.index')->with('success', "Invoice {$invoice->number} voided.");
    }
}
