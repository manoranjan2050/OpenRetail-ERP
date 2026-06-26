<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Services\LedgerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Customer::class);
        $customers = Customer::withSum([
                'ledgerEntries as debit_total' => fn($q) => $q->where('type', 'debit'),
                'ledgerEntries as credit_total' => fn($q) => $q->where('type', 'credit'),
            ], 'amount')
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('mobile', 'like', "%$s%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString()
            ->through(fn($c) => array_merge($c->toArray(), [
                'balance' => (float) ($c->debit_total - $c->credit_total),
            ]));

        return view('customers.index', [
            'customers' => $customers,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Customer::class);
        return view('customers.form');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Customer::class);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'billing_cycle' => 'required|in:none,weekly,monthly',
        ]);

        Customer::create($data);
        return redirect()->route('customers.index')->with('success', 'Customer created.');
    }

    public function show(Customer $customer): View
    {
        $this->authorize('view', $customer);
        $ledger = $customer->ledgerEntries()->with('creator')->latest('id')->paginate(30);
        return view('customers.show', [
            'customer' => $customer->append('balance'),
            'ledger' => $ledger,
            'invoices' => $customer->invoices()->latest()->limit(10)->get(),
        ]);
    }

    public function edit(Customer $customer): View
    {
        $this->authorize('update', $customer);
        return view('customers.form', ['customer' => $customer]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorize('update', $customer);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'billing_cycle' => 'required|in:none,weekly,monthly',
        ]);

        $customer->update($data);
        return redirect()->route('customers.index')->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorize('delete', $customer);
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted.');
    }

    public function recordPayment(Request $request, Customer $customer, LedgerService $ledger): RedirectResponse
    {
        $this->authorize('create', Payment::class);
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'mode' => 'required|in:cash,upi,card,bank_transfer,other',
            'reference' => 'nullable|string|max:100',
            'note' => 'nullable|string|max:255',
            'invoice_id' => 'nullable|exists:invoices,id',
        ]);

        $payment = Payment::create([
            'customer_id' => $customer->id,
            'invoice_id' => $data['invoice_id'] ?? null,
            'amount' => $data['amount'],
            'mode' => $data['mode'],
            'reference' => $data['reference'] ?? null,
            'received_by' => Auth::id(),
            'received_at' => now(),
            'note' => $data['note'] ?? null,
        ]);

        $ledger->credit($customer, $data['amount'], 'payment', $payment->id, "Payment received via {$data['mode']}");

        return back()->with('success', 'Payment recorded.');
    }
}
