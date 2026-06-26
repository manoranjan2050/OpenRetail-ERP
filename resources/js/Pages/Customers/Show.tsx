import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';

interface LedgerEntry { id: number; type: string; source: string; amount: number; balance_after: number; narration?: string; created_at: string; creator?: { name: string } }
interface Invoice { id: number; number: string; grand_total: number; status: string; invoice_date: string }
interface Customer { id: number; name: string; mobile?: string; email?: string; address?: string; credit_limit: number; billing_cycle: string; balance: number }

export default function CustomerShow({ customer, ledger, invoices }: {
    customer: Customer;
    ledger: { data: LedgerEntry[]; links: any[] };
    invoices: Invoice[];
}) {
    const [showPayment, setShowPayment] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({
        amount: '', mode: 'cash', reference: '', note: '', invoice_id: '',
    });
    const fmt = (n: number) => '₹' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2 });

    const submitPayment = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('customers.payment', customer.id), { onSuccess: () => { reset(); setShowPayment(false); } });
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800 dark:text-gray-200">Khata — {customer.name}</h2>}>
            <Head title={`Khata — ${customer.name}`} />
            <div className="py-6 px-4 max-w-5xl mx-auto space-y-6">

                {/* Customer card */}
                <div className="bg-white dark:bg-gray-800 rounded-xl shadow p-6 flex flex-wrap gap-6 justify-between">
                    <div>
                        <div className="text-lg font-bold text-gray-800 dark:text-gray-200">{customer.name}</div>
                        <div className="text-sm text-gray-500">{customer.mobile} {customer.email && `· ${customer.email}`}</div>
                        <div className="text-sm text-gray-500 mt-1 capitalize">Billing: {customer.billing_cycle} · Limit: {customer.credit_limit > 0 ? fmt(customer.credit_limit) : 'None'}</div>
                    </div>
                    <div className="text-right">
                        <div className="text-sm text-gray-500">Outstanding Balance</div>
                        <div className={`text-3xl font-bold ${customer.balance > 0 ? 'text-red-600' : 'text-green-600'}`}>{fmt(customer.balance)}</div>
                        <button onClick={() => setShowPayment(!showPayment)}
                            className="mt-2 bg-green-600 hover:bg-green-700 text-white px-4 py-1.5 rounded-lg text-sm">
                            Record Payment
                        </button>
                    </div>
                </div>

                {/* Payment form */}
                {showPayment && (
                    <div className="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 className="font-semibold text-gray-700 dark:text-gray-300 mb-4">Record Payment</h3>
                        <form onSubmit={submitPayment} className="grid grid-cols-2 gap-4">
                            {[{ label: 'Amount (₹) *', key: 'amount', type: 'number' }, { label: 'Reference', key: 'reference', type: 'text' }, { label: 'Note', key: 'note', type: 'text' }].map(f => (
                                <div key={f.key}>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{f.label}</label>
                                    <input type={f.type} step={f.key === 'amount' ? '0.01' : undefined}
                                        className="w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        value={(data as any)[f.key]} onChange={e => setData(f.key as any, e.target.value)} required={f.key === 'amount'} />
                                    {(errors as any)[f.key] && <p className="text-red-500 text-xs mt-1">{(errors as any)[f.key]}</p>}
                                </div>
                            ))}
                            <div>
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mode</label>
                                <select className="w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    value={data.mode} onChange={e => setData('mode', e.target.value)}>
                                    {['cash', 'upi', 'card', 'bank_transfer', 'other'].map(m => <option key={m} value={m} className="capitalize">{m}</option>)}
                                </select>
                            </div>
                            <div className="col-span-2 flex gap-3">
                                <button type="submit" disabled={processing} className="bg-green-600 text-white px-6 py-2 rounded-lg text-sm disabled:opacity-50">
                                    {processing ? 'Saving…' : 'Save Payment'}
                                </button>
                                <button type="button" onClick={() => setShowPayment(false)} className="text-gray-500 px-4 py-2 text-sm">Cancel</button>
                            </div>
                        </form>
                    </div>
                )}

                {/* Ledger */}
                <div className="bg-white dark:bg-gray-800 rounded-xl shadow overflow-x-auto">
                    <div className="px-6 py-4 border-b border-gray-100 dark:border-gray-700 font-semibold text-gray-700 dark:text-gray-300">Ledger History</div>
                    <table className="w-full text-sm">
                        <thead className="bg-gray-50 dark:bg-gray-700">
                            <tr className="text-left text-gray-500 dark:text-gray-400">
                                <th className="px-4 py-3">Date</th><th className="px-4 py-3">Type</th>
                                <th className="px-4 py-3">Narration</th><th className="px-4 py-3">Debit</th>
                                <th className="px-4 py-3">Credit</th><th className="px-4 py-3">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            {ledger.data.map(e => (
                                <tr key={e.id} className="border-t border-gray-100 dark:border-gray-700">
                                    <td className="px-4 py-2 text-gray-500">{new Date(e.created_at).toLocaleDateString('en-IN')}</td>
                                    <td className="px-4 py-2">
                                        <span className={`px-2 py-0.5 rounded-full text-xs ${e.type === 'debit' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'}`}>{e.type}</span>
                                    </td>
                                    <td className="px-4 py-2 text-gray-600 dark:text-gray-300">{e.narration ?? e.source}</td>
                                    <td className="px-4 py-2 text-red-600">{e.type === 'debit' ? fmt(e.amount) : ''}</td>
                                    <td className="px-4 py-2 text-green-600">{e.type === 'credit' ? fmt(e.amount) : ''}</td>
                                    <td className="px-4 py-2 font-semibold">{fmt(e.balance_after)}</td>
                                </tr>
                            ))}
                            {ledger.data.length === 0 && (
                                <tr><td colSpan={6} className="px-4 py-8 text-center text-gray-400">No ledger entries yet.</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
