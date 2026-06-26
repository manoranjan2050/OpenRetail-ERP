<?php

namespace App\Services;

use App\Models\BusinessSetting;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function __construct(private LedgerService $ledger) {}

    public function create(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $settings = BusinessSetting::instance();
            $number = $settings->invoice_prefix . str_pad($settings->invoice_next_number, 5, '0', STR_PAD_LEFT);
            $settings->increment('invoice_next_number');

            $subtotal = 0;
            $taxTotal = 0;
            $items = [];

            foreach ($data['items'] as $row) {
                $product = Product::findOrFail($row['product_id']);
                $qty = (float) $row['qty'];
                $price = (float) $row['unit_price'];
                $gst = (float) $product->gst_rate;
                $lineTax = round(($price * $qty * $gst) / 100, 2);
                $lineTotal = round($price * $qty + $lineTax, 2);

                $subtotal += $price * $qty;
                $taxTotal += $lineTax;
                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'qty' => $qty,
                    'unit_price' => $price,
                    'gst_rate' => $gst,
                    'line_tax' => $lineTax,
                    'line_total' => $lineTotal,
                ];
            }

            $discount = (float) ($data['discount'] ?? 0);
            $grandTotal = round($subtotal + $taxTotal - $discount, 2);
            $paidAmount = (float) ($data['paid_amount'] ?? $grandTotal);
            $mode = $data['payment_mode'] ?? 'cash';
            $status = match (true) {
                $mode === 'credit' => 'credit',
                $paidAmount >= $grandTotal => 'paid',
                $paidAmount > 0 => 'partial',
                default => 'credit',
            };

            $customer = isset($data['customer_id']) ? Customer::find($data['customer_id']) : null;

            // Credit limit check
            if ($customer && in_array($status, ['credit', 'partial'])) {
                $outstanding = $customer->balance + ($grandTotal - $paidAmount);
                if ($customer->credit_limit > 0 && $outstanding > $customer->credit_limit) {
                    throw new \RuntimeException("Credit limit exceeded. Outstanding would be ₹{$outstanding}, limit is ₹{$customer->credit_limit}.");
                }
            }

            $dueDate = null;
            if ($customer && $status === 'credit') {
                $dueDate = match ($customer->billing_cycle) {
                    'weekly' => now()->addWeek(),
                    'monthly' => now()->addMonth(),
                    default => now()->addDays(30),
                };
            }

            $invoice = Invoice::create([
                'number' => $number,
                'store_id' => $data['store_id'] ?? null,
                'customer_id' => $customer?->id,
                'salesman_id' => Auth::id(),
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'discount' => $discount,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'payment_mode' => $mode,
                'status' => $status,
                'invoice_date' => $data['invoice_date'] ?? today(),
                'due_date' => $dueDate,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $invoice->items()->create($item);
                // Deduct stock
                $product = Product::find($item['product_id']);
                $newQty = $product->stock_qty - $item['qty'];
                $product->update(['stock_qty' => $newQty]);
                StockMovement::create([
                    'product_id' => $product->id,
                    'change_qty' => -$item['qty'],
                    'balance_qty' => $newQty,
                    'type' => 'sale',
                    'ref_id' => $invoice->id,
                    'note' => "Invoice {$number}",
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                ]);
            }

            // Ledger entry for credit sales
            if ($customer && in_array($status, ['credit', 'partial'])) {
                $creditDue = $grandTotal - $paidAmount;
                if ($creditDue > 0) {
                    $this->ledger->debit($customer, $creditDue, 'invoice', $invoice->id, "Invoice {$number}");
                }
            }

            return $invoice;
        });
    }
}
