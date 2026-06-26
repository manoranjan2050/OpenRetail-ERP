import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';

interface Product { id: number; name: string; sale_price: number; gst_rate: number; stock_qty: number; unit_id?: number }
interface Customer { id: number; name: string; mobile?: string; credit_limit: number; billing_cycle: string }
interface Item { product_id: number; product_name: string; qty: number; unit_price: number; gst_rate: number; line_tax: number; line_total: number; [key: string]: unknown }

export default function InvoiceCreate({ customers, products, stores, settings }: { customers: Customer[]; products: Product[]; stores: any[]; settings: any }) {
    const [customer_id, setCustomerId] = useState('');
    const [items, setItems] = useState<Item[]>([]);
    const [payment_mode, setPaymentMode] = useState('cash');
    const [discount, setDiscount] = useState(0);
    const [paid_amount, setPaidAmount] = useState(0);
    const [notes, setNotes] = useState('');
    const [store_id, setStoreId] = useState(stores[0]?.id ?? '');
    const [processing, setProcessing] = useState(false);
    const [errors, setErrors] = useState<any>({});

    const addProduct = (productId: number) => {
        const p = products.find(x => x.id === productId);
        if (!p) return;
        const existing = items.findIndex(i => i.product_id === productId);
        if (existing >= 0) {
            updateQty(existing, items[existing].qty + 1);
            return;
        }
        const qty = 1;
        const gst = p.gst_rate;
        const lineTax = (p.sale_price * qty * gst) / 100;
        setItems([...items, { product_id: p.id, product_name: p.name, qty, unit_price: p.sale_price, gst_rate: gst, line_tax: lineTax, line_total: p.sale_price * qty + lineTax }]);
    };

    const updateQty = (idx: number, qty: number) => {
        setItems(items.map((item, i) => {
            if (i !== idx) return item;
            const lineTax = (item.unit_price * qty * item.gst_rate) / 100;
            return { ...item, qty, line_tax: lineTax, line_total: item.unit_price * qty + lineTax };
        }));
    };

    const updatePrice = (idx: number, price: number) => {
        setItems(items.map((item, i) => {
            if (i !== idx) return item;
            const lineTax = (price * item.qty * item.gst_rate) / 100;
            return { ...item, unit_price: price, line_tax: lineTax, line_total: price * item.qty + lineTax };
        }));
    };

    const subtotal = items.reduce((s, i) => s + i.unit_price * i.qty, 0);
    const taxTotal = items.reduce((s, i) => s + i.line_tax, 0);
    const grandTotal = Math.max(subtotal + taxTotal - discount, 0);

    useEffect(() => { if (payment_mode !== 'credit') setPaidAmount(grandTotal); }, [grandTotal, payment_mode]);

    const fmt = (n: number) => '₹' + n.toFixed(2);

    const submit = () => {
        setProcessing(true);
        router.post(route('invoices.store'), {
            customer_id: customer_id || null, store_id: store_id || null,
            items: items as any, payment_mode, discount, paid_amount, notes,
        }, {
            onError: (e) => { setErrors(e); setProcessing(false); },
            onFinish: () => setProcessing(false),
        });
    };

    const inp = "border rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white w-full focus:outline-none focus:ring-2 focus:ring-indigo-500";

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800 dark:text-gray-200">New Invoice / POS</h2>}>
            <Head title="New Invoice" />
            <div className="py-6 px-4 max-w-6xl mx-auto grid lg:grid-cols-3 gap-6">

                {/* Left: Items */}
                <div className="lg:col-span-2 space-y-4">
                    {/* Product search */}
                    <div className="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Add Product</label>
                        <select className={inp} onChange={e => { addProduct(Number(e.target.value)); e.target.value = ''; }} defaultValue="">
                            <option value="" disabled>Search and add product…</option>
                            {products.map(p => (
                                <option key={p.id} value={p.id}>{p.name} — ₹{p.sale_price} (Stock: {p.stock_qty})</option>
                            ))}
                        </select>
                    </div>

                    {/* Items table */}
                    <div className="bg-white dark:bg-gray-800 rounded-xl shadow overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 dark:bg-gray-700">
                                <tr className="text-left text-gray-500 dark:text-gray-400">
                                    <th className="px-3 py-3">Product</th><th className="px-3 py-3 w-20">Qty</th>
                                    <th className="px-3 py-3 w-28">Rate (₹)</th><th className="px-3 py-3">GST</th>
                                    <th className="px-3 py-3">Tax</th><th className="px-3 py-3">Total</th><th className="px-3 py-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                {items.map((item, idx) => (
                                    <tr key={idx} className="border-t border-gray-100 dark:border-gray-700">
                                        <td className="px-3 py-2 text-gray-800 dark:text-gray-200">{item.product_name}</td>
                                        <td className="px-3 py-2"><input type="number" step="0.001" min="0.001" value={item.qty} onChange={e => updateQty(idx, Number(e.target.value))} className="border rounded px-2 py-1 w-20 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" /></td>
                                        <td className="px-3 py-2"><input type="number" step="0.01" min="0" value={item.unit_price} onChange={e => updatePrice(idx, Number(e.target.value))} className="border rounded px-2 py-1 w-24 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" /></td>
                                        <td className="px-3 py-2 text-gray-500">{item.gst_rate}%</td>
                                        <td className="px-3 py-2 text-gray-500">{fmt(item.line_tax)}</td>
                                        <td className="px-3 py-2 font-semibold">{fmt(item.line_total)}</td>
                                        <td className="px-3 py-2"><button onClick={() => setItems(items.filter((_, i) => i !== idx))} className="text-red-500 hover:text-red-700 text-lg">×</button></td>
                                    </tr>
                                ))}
                                {items.length === 0 && <tr><td colSpan={7} className="px-4 py-8 text-center text-gray-400">Add products above</td></tr>}
                            </tbody>
                        </table>
                    </div>
                    {errors.items && <p className="text-red-500 text-sm">{errors.items}</p>}
                </div>

                {/* Right: Summary */}
                <div className="space-y-4">
                    <div className="bg-white dark:bg-gray-800 rounded-xl shadow p-4 space-y-3">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Customer</label>
                            <select className={inp} value={customer_id} onChange={e => setCustomerId(e.target.value)}>
                                <option value="">Walk-in (no account)</option>
                                {customers.map(c => <option key={c.id} value={c.id}>{c.name} {c.mobile ? `· ${c.mobile}` : ''}</option>)}
                            </select>
                        </div>
                        {stores.length > 1 && (
                            <div>
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Store</label>
                                <select className={inp} value={store_id} onChange={e => setStoreId(e.target.value)}>
                                    {stores.map(s => <option key={s.id} value={s.id}>{s.name}</option>)}
                                </select>
                            </div>
                        )}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Mode</label>
                            <select className={inp} value={payment_mode} onChange={e => setPaymentMode(e.target.value)}>
                                {['cash', 'upi', 'card', 'credit', 'mixed'].map(m => <option key={m} value={m} className="capitalize">{m}</option>)}
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Discount (₹)</label>
                            <input type="number" step="0.01" min="0" className={inp} value={discount} onChange={e => setDiscount(Number(e.target.value))} />
                        </div>
                        {payment_mode !== 'credit' && (
                            <div>
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount Paid (₹)</label>
                                <input type="number" step="0.01" min="0" className={inp} value={paid_amount} onChange={e => setPaidAmount(Number(e.target.value))} />
                            </div>
                        )}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                            <textarea className={inp} rows={2} value={notes} onChange={e => setNotes(e.target.value)} />
                        </div>
                    </div>

                    {/* Totals */}
                    <div className="bg-white dark:bg-gray-800 rounded-xl shadow p-4 space-y-2 text-sm">
                        <div className="flex justify-between"><span className="text-gray-500">Subtotal</span><span>{fmt(subtotal)}</span></div>
                        <div className="flex justify-between"><span className="text-gray-500">GST</span><span>{fmt(taxTotal)}</span></div>
                        {discount > 0 && <div className="flex justify-between"><span className="text-gray-500">Discount</span><span className="text-red-500">−{fmt(discount)}</span></div>}
                        <div className="flex justify-between font-bold text-base border-t pt-2"><span>Grand Total</span><span>{fmt(grandTotal)}</span></div>
                        {payment_mode === 'credit' && <div className="flex justify-between text-red-600"><span>Credit Due</span><span>{fmt(grandTotal)}</span></div>}
                        {errors.credit && <p className="text-red-500 text-xs">{errors.credit}</p>}
                    </div>

                    <button onClick={submit} disabled={processing || items.length === 0}
                        className="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl text-base font-semibold disabled:opacity-50">
                        {processing ? 'Creating…' : 'Create Invoice'}
                    </button>

                    {/* UPI QR hint */}
                    {settings.show_qr_on_invoice && settings.upi_id && payment_mode === 'upi' && (
                        <div className="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 rounded-xl p-3 text-xs text-yellow-700 dark:text-yellow-400">
                            UPI QR will be generated on the invoice PDF after saving.
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
