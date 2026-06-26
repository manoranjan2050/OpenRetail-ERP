import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

interface Customer { id: number; name: string; mobile?: string; email?: string; credit_limit: number; billing_cycle: string; balance: number; is_active: boolean }

export default function CustomersIndex({ customers, filters }: { customers: { data: Customer[]; links: any[] }; filters: any }) {
    const [search, setSearch] = useState(filters.search ?? '');
    const fmt = (n: number) => '₹' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2 });

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800 dark:text-gray-200">Customers</h2>}>
            <Head title="Customers" />
            <div className="py-6 px-4 max-w-7xl mx-auto space-y-4">
                <div className="flex gap-3 justify-between">
                    <div className="flex gap-2">
                        <input value={search} onChange={e => setSearch(e.target.value)}
                            onKeyDown={e => e.key === 'Enter' && router.get(route('customers.index'), { search }, { preserveState: true })}
                            placeholder="Search name or mobile..."
                            className="border rounded-lg px-3 py-2 text-sm w-60 dark:bg-gray-800 dark:border-gray-600 dark:text-white" />
                    </div>
                    <Link href={route('customers.create')} className="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">+ Add Customer</Link>
                </div>

                <div className="bg-white dark:bg-gray-800 rounded-xl shadow overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead className="bg-gray-50 dark:bg-gray-700">
                            <tr className="text-left text-gray-500 dark:text-gray-400">
                                <th className="px-4 py-3">Name</th><th className="px-4 py-3">Mobile</th>
                                <th className="px-4 py-3">Billing</th><th className="px-4 py-3">Credit Limit</th>
                                <th className="px-4 py-3">Balance Due</th><th className="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {customers.data.map(c => (
                                <tr key={c.id} className="border-t border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750">
                                    <td className="px-4 py-3 font-medium text-gray-800 dark:text-gray-200">{c.name}</td>
                                    <td className="px-4 py-3 text-gray-500">{c.mobile ?? '-'}</td>
                                    <td className="px-4 py-3 capitalize text-gray-500">{c.billing_cycle}</td>
                                    <td className="px-4 py-3">{c.credit_limit > 0 ? fmt(c.credit_limit) : '—'}</td>
                                    <td className={`px-4 py-3 font-semibold ${c.balance > 0 ? 'text-red-600' : 'text-green-600'}`}>
                                        {c.balance > 0 ? fmt(c.balance) : '—'}
                                    </td>
                                    <td className="px-4 py-3 flex gap-2">
                                        <Link href={route('customers.show', c.id)} className="text-indigo-600 hover:underline text-sm">Ledger</Link>
                                        <Link href={route('customers.edit', c.id)} className="text-gray-600 hover:underline text-sm">Edit</Link>
                                    </td>
                                </tr>
                            ))}
                            {customers.data.length === 0 && (
                                <tr><td colSpan={6} className="px-4 py-8 text-center text-gray-400">No customers found.</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
                <div className="flex gap-1">
                    {customers.links.map((l: any, i: number) => (
                        <Link key={i} href={l.url ?? '#'} preserveScroll
                            className={`px-3 py-1 rounded text-sm border ${l.active ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-300 hover:bg-gray-50'} ${!l.url ? 'opacity-40 pointer-events-none' : ''}`}
                            dangerouslySetInnerHTML={{ __html: l.label }} />
                    ))}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
