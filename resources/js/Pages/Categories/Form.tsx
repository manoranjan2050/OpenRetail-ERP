import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function CategoryForm({ category }: { category?: any }) {
    const { data, setData, post, put, processing, errors } = useForm({ name: category?.name ?? '', description: category?.description ?? '' });
    const submit = (e: React.FormEvent) => { e.preventDefault(); category?.id ? put(route('categories.update', category.id)) : post(route('categories.store')); };
    const inp = "w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500";
    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800 dark:text-gray-200">{category ? 'Edit' : 'Add'} Category</h2>}>
            <Head title="Category" />
            <div className="py-6 px-4 max-w-md mx-auto">
                <form onSubmit={submit} className="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-4">
                    <div><label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name *</label><input className={inp} value={data.name} onChange={e => setData('name', e.target.value)} required />{errors.name && <p className="text-red-500 text-xs mt-1">{errors.name}</p>}</div>
                    <div><label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label><textarea className={inp} rows={2} value={data.description} onChange={e => setData('description', e.target.value)} /></div>
                    <div className="flex gap-3"><button type="submit" disabled={processing} className="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm disabled:opacity-50">{processing ? 'Saving…' : 'Save'}</button><a href={route('categories.index')} className="text-gray-500 px-4 py-2 text-sm">Cancel</a></div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
