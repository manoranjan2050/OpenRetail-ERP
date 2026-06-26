@extends('layouts.app')
@section('title', 'New Invoice / POS')
@section('header', 'New Invoice — POS')

@section('content')
<div x-data="pos(@json($products), @json($customers))" class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Left: Product Picker + Items --}}
    <div class="xl:col-span-2 space-y-4">
        {{-- Product Search --}}
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
            <label class="block text-xs text-slate-400 mb-2 font-medium">Add Product</label>
            <div class="relative">
                <input type="text" x-model="productSearch" @input.debounce.200ms="filterProducts()"
                       placeholder="Type product name to search..."
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-slate-500">

                <div x-show="productSearch.length > 0 && filteredProducts.length > 0" x-cloak
                     class="absolute top-full left-0 right-0 mt-1 bg-slate-800 border border-slate-700 rounded-lg shadow-xl z-40 max-h-64 overflow-y-auto">
                    <template x-for="p in filteredProducts.slice(0, 20)" :key="p.id">
                        <button type="button" @click="addItem(p)"
                                class="w-full text-left px-4 py-2.5 text-sm hover:bg-slate-700 border-b border-slate-700/50 last:border-0">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-slate-200 font-medium" x-text="p.name"></span>
                                    <span class="ml-2 text-xs text-slate-500" x-text="'Stock: ' + p.stock_qty"></span>
                                </div>
                                <span class="text-emerald-400 font-medium text-xs" x-text="'₹' + parseFloat(p.sale_price).toFixed(2)"></span>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        {{-- Items Table --}}
        <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-800 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-300">Invoice Items</h3>
                <span class="text-xs text-slate-500" x-text="items.length + ' item(s)'"></span>
            </div>
            <div x-show="items.length === 0" class="px-4 py-8 text-center text-slate-600 text-sm">
                No items added yet. Search and add products above.
            </div>
            <table class="w-full text-sm" x-show="items.length > 0">
                <thead class="bg-slate-800/50">
                    <tr class="text-xs text-slate-400 uppercase tracking-wider">
                        <th class="text-left px-4 py-2">Product</th>
                        <th class="text-right px-3 py-2">Qty</th>
                        <th class="text-right px-3 py-2">Rate ₹</th>
                        <th class="text-right px-3 py-2">GST %</th>
                        <th class="text-right px-3 py-2">Total ₹</th>
                        <th class="px-2 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(item, idx) in items" :key="idx">
                        <tr class="border-t border-slate-800">
                            <td class="px-4 py-2 text-slate-200" x-text="item.name"></td>
                            <td class="px-3 py-2 text-right">
                                <input type="number" x-model.number="item.qty" @change="item.qty = Math.max(0.001, item.qty); calcTotals()"
                                       step="0.001" min="0.001"
                                       class="w-20 bg-slate-800 border border-slate-700 text-slate-200 rounded px-2 py-1 text-xs text-right focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            </td>
                            <td class="px-3 py-2 text-right">
                                <input type="number" x-model.number="item.unit_price" @change="calcTotals()"
                                       step="0.01" min="0"
                                       class="w-24 bg-slate-800 border border-slate-700 text-slate-200 rounded px-2 py-1 text-xs text-right focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            </td>
                            <td class="px-3 py-2 text-right text-slate-400 text-xs" x-text="item.gst_rate + '%'"></td>
                            <td class="px-3 py-2 text-right text-slate-200 font-medium text-xs" x-text="'₹' + lineTotal(item).toFixed(2)"></td>
                            <td class="px-2 py-2">
                                <button type="button" @click="items.splice(idx, 1); calcTotals()"
                                        class="text-red-500 hover:text-red-400 text-xs">&times;</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Right: Payment + Submit --}}
    <div class="space-y-4">
        <form method="POST" action="{{ route('invoices.store') }}" id="invoiceForm" @submit.prevent="submitInvoice()">
            @csrf
            {{-- Hidden fields populated by Alpine --}}
            <div id="hiddenFields"></div>

            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-4">
                <h3 class="text-sm font-semibold text-slate-300">Order Summary</h3>

                {{-- Customer --}}
                <div>
                    <label class="block text-xs text-slate-400 mb-1 font-medium">Customer (optional)</label>
                    <select name="customer_id" x-model="selectedCustomerId"
                            class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Walk-in Customer</option>
                        <template x-for="c in customers" :key="c.id">
                            <option :value="c.id" x-text="c.name + (c.mobile ? ' - ' + c.mobile : '')"></option>
                        </template>
                    </select>
                </div>

                @if(count($stores) > 0)
                {{-- Store --}}
                <div>
                    <label class="block text-xs text-slate-400 mb-1 font-medium">Store</label>
                    <select name="store_id"
                            class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Default</option>
                        @foreach($stores as $store)
                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Payment Mode --}}
                <div>
                    <label class="block text-xs text-slate-400 mb-1 font-medium">Payment Mode <span class="text-red-400">*</span></label>
                    <select name="payment_mode" x-model="paymentMode"
                            class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="cash">Cash</option>
                        <option value="upi">UPI</option>
                        <option value="card">Card</option>
                        <option value="credit">Credit</option>
                        <option value="mixed">Mixed</option>
                    </select>
                </div>

                {{-- Discount --}}
                <div>
                    <label class="block text-xs text-slate-400 mb-1 font-medium">Discount (₹)</label>
                    <input type="number" name="discount" x-model.number="discount" @change="calcTotals()"
                           step="0.01" min="0" value="0"
                           class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                {{-- Paid Amount --}}
                <div x-show="paymentMode !== 'credit'">
                    <label class="block text-xs text-slate-400 mb-1 font-medium">Amount Paid (₹)</label>
                    <input type="number" name="paid_amount" x-model.number="paidAmount"
                           step="0.01" min="0"
                           class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-xs text-slate-400 mb-1 font-medium">Notes</label>
                    <textarea name="notes" rows="2"
                              class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
                </div>

                {{-- Totals --}}
                <div class="border-t border-slate-800 pt-3 space-y-2 text-sm">
                    <div class="flex justify-between text-slate-400">
                        <span>Subtotal</span>
                        <span x-text="'₹' + subtotal.toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between text-slate-400">
                        <span>GST</span>
                        <span x-text="'₹' + taxTotal.toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between text-slate-400" x-show="discount > 0">
                        <span>Discount</span>
                        <span class="text-rose-400" x-text="'-₹' + parseFloat(discount).toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between text-lg font-bold text-white border-t border-slate-700 pt-2">
                        <span>Grand Total</span>
                        <span x-text="'₹' + grandTotal.toFixed(2)"></span>
                    </div>
                    <div x-show="paymentMode !== 'credit'" class="flex justify-between text-emerald-400 font-medium">
                        <span>Balance</span>
                        <span x-text="'₹' + Math.max(0, grandTotal - paidAmount).toFixed(2)"></span>
                    </div>
                </div>

                {{-- Submit --}}
                <button type="button" @click="submitInvoice()"
                        :disabled="items.length === 0"
                        class="w-full bg-indigo-600 hover:bg-indigo-500 disabled:opacity-40 disabled:cursor-not-allowed text-white font-semibold py-3 rounded-lg transition-colors">
                    Create Invoice
                </button>

                @error('credit')
                <p class="text-red-400 text-xs">{{ $message }}</p>
                @enderror
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function pos(allProducts, allCustomers) {
    return {
        products: allProducts,
        customers: allCustomers,
        productSearch: '',
        filteredProducts: [],
        items: [],
        selectedCustomerId: '',
        paymentMode: 'cash',
        discount: 0,
        paidAmount: 0,
        subtotal: 0,
        taxTotal: 0,
        grandTotal: 0,

        filterProducts() {
            const q = this.productSearch.toLowerCase();
            this.filteredProducts = this.products.filter(p =>
                p.name.toLowerCase().includes(q)
            );
        },

        addItem(product) {
            const existing = this.items.find(i => i.product_id === product.id);
            if (existing) {
                existing.qty += 1;
            } else {
                this.items.push({
                    product_id: product.id,
                    name: product.name,
                    qty: 1,
                    unit_price: parseFloat(product.sale_price),
                    gst_rate: parseFloat(product.gst_rate),
                });
            }
            this.productSearch = '';
            this.filteredProducts = [];
            this.calcTotals();
        },

        lineTotal(item) {
            const base = item.qty * item.unit_price;
            const tax = base * (item.gst_rate / 100);
            return base + tax;
        },

        calcTotals() {
            let sub = 0, tax = 0;
            for (const item of this.items) {
                const base = item.qty * item.unit_price;
                sub += base;
                tax += base * (item.gst_rate / 100);
            }
            this.subtotal = sub;
            this.taxTotal = tax;
            this.grandTotal = Math.max(0, sub + tax - parseFloat(this.discount || 0));
            if (this.paymentMode !== 'credit' && this.paidAmount === 0) {
                this.paidAmount = this.grandTotal;
            }
        },

        submitInvoice() {
            if (this.items.length === 0) return alert('Add at least one product.');

            const form = document.getElementById('invoiceForm');
            const container = document.getElementById('hiddenFields');
            container.innerHTML = '';

            const addHidden = (name, value) => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = name;
                inp.value = value;
                container.appendChild(inp);
            };

            this.items.forEach((item, i) => {
                addHidden(`items[${i}][product_id]`, item.product_id);
                addHidden(`items[${i}][qty]`, item.qty);
                addHidden(`items[${i}][unit_price]`, item.unit_price);
            });

            if (this.selectedCustomerId) addHidden('customer_id', this.selectedCustomerId);
            addHidden('payment_mode', this.paymentMode);
            addHidden('discount', this.discount || 0);
            addHidden('paid_amount', this.paidAmount || 0);

            form.submit();
        }
    }
}
</script>
@endpush
