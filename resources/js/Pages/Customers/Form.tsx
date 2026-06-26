import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

interface Customer { id?: number; name: string; mobile?: string; email?: string; address?: string; credit_limit: number; billing_cycle: string }

export default function CustomerForm({ customer }: { customer?: Customer }) {
    const { data, setData, post, put, processing, errors } = useForm({
        name: customer?.name ?? '', mobile: customer?.mobile ?? '', email: customer?.email ?? '',
        address: customer?.address ?? '', credit_limit: customer?.credit_limit ?? 0,
        billing_cycle: customer?.billing_cycle ?? 'none',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        customer?.id ? put(route('customers.update', customer.id)) : post(route('customers.store'));
    };

    const inp = "w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500";

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800 dark:text-gray-200">{customer ? 'Edit Customer' : 'Add Customer'}</h2>}>
            <Head title={customer ? 'Edit Customer' : 'Add Customer'} />
            <div className="py-6 px-4 max-w-2xl mx-auto">
                <form onSubmit={submit} className="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-4">
                    {[
                        { label: 'Name *', key: 'name', type: 'text', required: true },
                        { label: 'Mobile', key: 'mobile', type: 'tel' },
                        { label: 'Email', key: 'email', type: 'email' },
                    ].map(f => (
                        <div key={f.key}>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{f.label}</label>
                            <input type={f.type} className={inp} value={(data as any)[f.key]} required={f.required}
                                onChange={e => setData(f.key as any, e.target.value)} />
                            {(errors as any)[f.key] && <p className="text-red-500 text-xs mt-1">{(errors as any)[f.key]}</p>}
                        </div>
                    ))}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
                        <textarea className={inp} rows={2} value={data.address} onChange={e => setData('address', e.target.value)} />
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Credit Limit (₹)</label>
                            <input type="number" step="0.01" className={inp} value={data.credit_limit} onChange={e => setData('credit_limit', Number(e.target.value))} />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Billing Cycle</label>
                            <select className={inp} value={data.billing_cycle} onChange={e => setData('billing_cycle', e.target.value)}>
                                <option value="none">None (cash)</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                    </div>
                    <div className="flex gap-3 pt-2">
                        <button type="submit" disabled={processing} className="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg text-sm disabled:opacity-50">
                            {processing ? 'Saving…' : customer ? 'Update' : 'Create Customer'}
                        </button>
                        <a href={route('customers.index')} className="text-gray-500 hover:text-gray-700 px-4 py-2 text-sm">Cancel</a>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
