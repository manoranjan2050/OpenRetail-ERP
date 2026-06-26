import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

interface Invoice { id: number; number: string; grand_total: number; paid_amount: number; status: string; payment_mode: string; invoice_date: string; customer?: { name: string }; salesman: { name: string } }
const STATUS_COLOR: Record<string, string> = { paid: 'bg-green-100 text-green-700', partial: 'bg-orange-100 text-orange-700', credit: 'bg-yellow-100 text-yellow-700', void: 'bg-gray-100 text-gray-500' };

export default function InvoicesIndex({ invoices, filters }: { invoices: { data: Invoice[]; links: any[] }; filters: any }) {
    const [search, setSearch] = useState(filters.search ?? '');
    const fmt = (n: number) => '₹' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2 });

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800 dark:text-gray-200">Invoices</h2>}>
            <Head title="Invoices" />
            <div className="py-6 px-4 max-w-7xl mx-auto space-y-4">
                <div className="flex gap-3 justify-between flex-wrap">
                    <div className="flex gap-2">
                        <input value={search} onChange={e => setSearch(e.target.value)}
                            onKeyDown={e => e.key === 'Enter' && router.get(route('invoices.index'), { search }, { preserveState: true })}
                            placeholder="Invoice number..." className="border rounded-lg px-3 py-2 text-sm w-48 dark:bg-gray-800 dark:border-gray-600 dark:text-white" />
                        <select value={filters.status ?? ''} onChange={e => router.get(route('invoices.index'), { ...filters, status: e.target.value }, { preserveState: true })}
                            className="border rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            <option value="">All Status</option>
                            {['paid', 'partial', 'credit', 'void'].map(s => <option key={s} value={s} className="capitalize">{s}</option>)}
                        </select>
                    </div>
                    <Link href={route('invoices.create')} className="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">+ New Invoice</Link>
                </div>

                <div className="bg-white dark:bg-gray-800 rounded-xl shadow overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead className="bg-gray-50 dark:bg-gray-700">
                            <tr className="text-left text-gray-500 dark:text-gray-400">
                                <th className="px-4 py-3">Invoice #</th><th className="px-4 py-3">Date</th>
                                <th className="px-4 py-3">Customer</th><th className="px-4 py-3">Mode</th>
                                <th className="px-4 py-3">Total</th><th className="px-4 py-3">Paid</th>
                                <th className="px-4 py-3">Status</th><th className="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {invoices.data.map(inv => (
                                <tr key={inv.id} className="border-t border-gray-100 dark:border-gray-700 hover:bg-gray-50">
                                    <td className="px-4 py-3 font-mono font-semibold text-indigo-600">{inv.number}</td>
                                    <td className="px-4 py-3 text-gray-500">{new Date(inv.invoice_date).toLocaleDateString('en-IN')}</td>
                                    <td className="px-4 py-3">{inv.customer?.name ?? 'Walk-in'}</td>
                                    <td className="px-4 py-3 capitalize text-gray-500">{inv.payment_mode}</td>
                                    <td className="px-4 py-3 font-semibold">{fmt(inv.grand_total)}</td>
                                    <td className="px-4 py-3">{fmt(inv.paid_amount)}</td>
                                    <td className="px-4 py-3"><span className={`px-2 py-0.5 rounded-full text-xs capitalize ${STATUS_COLOR[inv.status]}`}>{inv.status}</span></td>
                                    <td className="px-4 py-3 flex gap-2">
                                        <Link href={route('invoices.show', inv.id)} className="text-indigo-600 hover:underline text-sm">View</Link>
                                        <a href={route('invoices.pdf', inv.id)} target="_blank" className="text-gray-500 hover:underline text-sm">PDF</a>
                                    </td>
                                </tr>
                            ))}
                            {invoices.data.length === 0 && (
                                <tr><td colSpan={8} className="px-4 py-8 text-center text-gray-400">No invoices found.</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
                <div className="flex gap-1">
                    {invoices.links.map((l: any, i: number) => (
                        <Link key={i} href={l.url ?? '#'} className={`px-3 py-1 rounded text-sm border ${l.active ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-300 hover:bg-gray-50'} ${!l.url ? 'opacity-40 pointer-events-none' : ''}`} dangerouslySetInnerHTML={{ __html: l.label }} />
                    ))}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
