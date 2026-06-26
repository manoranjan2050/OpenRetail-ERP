import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

interface InvoiceItem { id: number; product_name: string; qty: number; unit_price: number; gst_rate: number; line_tax: number; line_total: number; product?: { hsn_code?: string } }
interface Invoice { id: number; number: string; grand_total: number; paid_amount: number; subtotal: number; tax_total: number; discount: number; status: string; payment_mode: string; invoice_date: string; due_date?: string; notes?: string; customer?: { id: number; name: string; mobile?: string }; salesman: { name: string }; items: InvoiceItem[] }

const STATUS_COLOR: Record<string, string> = { paid: 'bg-green-100 text-green-700', partial: 'bg-orange-100 text-orange-700', credit: 'bg-yellow-100 text-yellow-700', void: 'bg-gray-100 text-gray-500' };
const fmt = (n: number) => '₹' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2 });

export default function InvoiceShow({ invoice, settings, qrCode }: { invoice: Invoice; settings: any; qrCode?: string }) {
    const voidInvoice = () => {
        if (!confirm('Void this invoice? This cannot be undone.')) return;
        router.post(route('invoices.void', invoice.id));
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800 dark:text-gray-200">Invoice {invoice.number}</h2>}>
            <Head title={`Invoice ${invoice.number}`} />
            <div className="py-6 px-4 max-w-4xl mx-auto space-y-6">
                {/* Actions */}
                <div className="flex gap-3 justify-between items-center">
                    <Link href={route('invoices.index')} className="text-gray-500 hover:text-gray-700 text-sm">← Back</Link>
                    <div className="flex gap-2">
                        <a href={route('invoices.pdf', invoice.id)} target="_blank" className="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm">Download PDF</a>
                        {invoice.status !== 'void' && (
                            <button onClick={voidInvoice} className="bg-red-100 hover:bg-red-200 text-red-700 px-4 py-2 rounded-lg text-sm">Void Invoice</button>
                        )}
                    </div>
                </div>

                <div className="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                    {/* Header */}
                    <div className="flex justify-between mb-6">
                        <div>
                            <div className="text-2xl font-bold text-gray-800 dark:text-gray-200">{settings.business_name}</div>
                            {settings.address && <div className="text-sm text-gray-500 mt-1">{settings.address}</div>}
                            {settings.gstin && <div className="text-sm text-gray-500">GSTIN: {settings.gstin}</div>}
                        </div>
                        <div className="text-right">
                            <div className="font-mono text-lg font-bold text-indigo-600">{invoice.number}</div>
                            <div className="text-sm text-gray-500">{new Date(invoice.invoice_date).toLocaleDateString('en-IN', { day: 'numeric', month: 'long', year: 'numeric' })}</div>
                            {invoice.due_date && <div className="text-sm text-red-500">Due: {new Date(invoice.due_date).toLocaleDateString('en-IN')}</div>}
                            <span className={`mt-1 inline-block px-3 py-0.5 rounded-full text-sm capitalize ${STATUS_COLOR[invoice.status]}`}>{invoice.status}</span>
                        </div>
                    </div>

                    {/* Bill to */}
                    {invoice.customer && (
                        <div className="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 mb-6 text-sm">
                            <div className="font-semibold text-gray-700 dark:text-gray-300">Bill To: {invoice.customer.name}</div>
                            {invoice.customer.mobile && <div className="text-gray-500">{invoice.customer.mobile}</div>}
                        </div>
                    )}

                    {/* Items */}
                    <table className="w-full text-sm mb-6">
                        <thead className="bg-gray-50 dark:bg-gray-700">
                            <tr className="text-left text-gray-500 dark:text-gray-400">
                                <th className="px-3 py-2">#</th><th className="px-3 py-2">Item</th><th className="px-3 py-2">Qty</th>
                                <th className="px-3 py-2">Rate</th><th className="px-3 py-2">GST</th><th className="px-3 py-2">Tax</th><th className="px-3 py-2">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            {invoice.items.map((item, i) => (
                                <tr key={item.id} className="border-t border-gray-100 dark:border-gray-700">
                                    <td className="px-3 py-2 text-gray-500">{i + 1}</td>
                                    <td className="px-3 py-2 font-medium text-gray-800 dark:text-gray-200">{item.product_name}</td>
                                    <td className="px-3 py-2">{item.qty}</td>
                                    <td className="px-3 py-2">{fmt(item.unit_price)}</td>
                                    <td className="px-3 py-2 text-gray-500">{item.gst_rate}%</td>
                                    <td className="px-3 py-2 text-gray-500">{fmt(item.line_tax)}</td>
                                    <td className="px-3 py-2 font-semibold">{fmt(item.line_total)}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>

                    {/* Totals + QR */}
                    <div className="flex justify-between items-start">
                        {qrCode && (
                            <div className="text-center">
                                <div className="text-xs text-gray-400 mb-1">Scan to pay via UPI</div>
                                <img src={`data:image/png;base64,${qrCode}`} className="w-28 h-28" alt="UPI QR" />
                                {settings.upi_id && <div className="text-xs text-gray-500 mt-1">{settings.upi_id}</div>}
                            </div>
                        )}
                        <div className="ml-auto space-y-1 text-sm min-w-48">
                            <div className="flex justify-between gap-8"><span className="text-gray-500">Subtotal</span><span>{fmt(invoice.subtotal)}</span></div>
                            <div className="flex justify-between gap-8"><span className="text-gray-500">GST</span><span>{fmt(invoice.tax_total)}</span></div>
                            {invoice.discount > 0 && <div className="flex justify-between gap-8"><span className="text-gray-500">Discount</span><span className="text-red-500">−{fmt(invoice.discount)}</span></div>}
                            <div className="flex justify-between gap-8 font-bold text-base border-t pt-1"><span>Grand Total</span><span>{fmt(invoice.grand_total)}</span></div>
                            <div className="flex justify-between gap-8 text-green-600"><span>Paid</span><span>{fmt(invoice.paid_amount)}</span></div>
                            {invoice.grand_total - invoice.paid_amount > 0 && (
                                <div className="flex justify-between gap-8 text-red-600 font-bold"><span>Balance Due</span><span>{fmt(invoice.grand_total - invoice.paid_amount)}</span></div>
                            )}
                        </div>
                    </div>

                    {invoice.notes && <div className="mt-4 text-sm text-gray-500 border-t pt-3"><strong>Notes:</strong> {invoice.notes}</div>}
                    <div className="mt-3 text-xs text-gray-400">Salesman: {invoice.salesman.name} · Mode: <span className="capitalize">{invoice.payment_mode}</span></div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
