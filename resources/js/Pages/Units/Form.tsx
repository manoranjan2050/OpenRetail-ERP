import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function UnitForm({ unit }: { unit?: any }) {
    const { data, setData, post, put, processing, errors } = useForm({ name: unit?.name ?? '', symbol: unit?.symbol ?? '' });
    const submit = (e: React.FormEvent) => { e.preventDefault(); unit?.id ? put(route('units.update', unit.id)) : post(route('units.store')); };
    const inp = "w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500";
    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800 dark:text-gray-200">{unit ? 'Edit' : 'Add'} Unit</h2>}>
            <Head title="Unit" />
            <div className="py-6 px-4 max-w-md mx-auto">
                <form onSubmit={submit} className="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-4">
                    <div><label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name *</label><input className={inp} value={data.name} onChange={e => setData('name', e.target.value)} required />{errors.name && <p className="text-red-500 text-xs mt-1">{errors.name}</p>}</div>
                    <div><label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Symbol *</label><input className={inp} value={data.symbol} onChange={e => setData('symbol', e.target.value)} required placeholder="pcs, kg, L…" />{errors.symbol && <p className="text-red-500 text-xs mt-1">{errors.symbol}</p>}</div>
                    <div className="flex gap-3"><button type="submit" disabled={processing} className="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm disabled:opacity-50">{processing ? 'Saving…' : 'Save'}</button><a href={route('units.index')} className="text-gray-500 px-4 py-2 text-sm">Cancel</a></div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
