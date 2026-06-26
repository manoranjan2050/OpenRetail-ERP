import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function CategoriesIndex({ categories }: { categories: any[] }) {
    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800 dark:text-gray-200">Product Categories</h2>}>
            <Head title="Categories" />
            <div className="py-6 px-4 max-w-3xl mx-auto space-y-4">
                <div className="flex justify-end"><Link href={route('categories.create')} className="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">+ Add Category</Link></div>
                <div className="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
                    <table className="w-full text-sm">
                        <thead className="bg-gray-50 dark:bg-gray-700"><tr className="text-left text-gray-500 dark:text-gray-400"><th className="px-4 py-3">Name</th><th className="px-4 py-3">Products</th><th className="px-4 py-3">Actions</th></tr></thead>
                        <tbody>
                            {categories.map(c => (
                                <tr key={c.id} className="border-t border-gray-100 dark:border-gray-700">
                                    <td className="px-4 py-3 font-medium text-gray-800 dark:text-gray-200">{c.name}</td>
                                    <td className="px-4 py-3 text-gray-500">{c.products_count}</td>
                                    <td className="px-4 py-3 flex gap-2">
                                        <Link href={route('categories.edit', c.id)} className="text-indigo-600 hover:underline text-sm">Edit</Link>
                                        <Link href={route('categories.destroy', c.id)} method="delete" as="button" className="text-red-500 hover:underline text-sm" onClick={e => !confirm('Delete?') && e.preventDefault()}>Delete</Link>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
