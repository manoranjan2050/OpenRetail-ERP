import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

interface Product {
    id: number; name: string; sku: string; sale_price: number; purchase_price: number;
    gst_rate: number; stock_qty: number; low_stock_threshold: number;
    is_active: boolean; category?: { name: string }; unit?: { symbol: string };
}
interface Props {
    products: { data: Product[]; links: any[]; meta: any };
    categories: { id: number; name: string }[];
    filters: { search?: string; category_id?: string; low_stock?: string };
}

export default function ProductsIndex({ products, categories, filters }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');

    const applyFilter = (extra: object = {}) => {
        router.get(route('products.index'), { search, ...filters, ...extra }, { preserveState: true, replace: true });
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800 dark:text-gray-200">Products</h2>}>
            <Head title="Products" />
            <div className="py-6 px-4 max-w-7xl mx-auto space-y-4">
                <div className="flex flex-wrap gap-3 items-center justify-between">
                    <div className="flex gap-2">
                        <input value={search} onChange={e => setSearch(e.target.value)}
                            onKeyDown={e => e.key === 'Enter' && applyFilter()}
                            placeholder="Search name or SKU..."
                            className="border rounded-lg px-3 py-2 text-sm w-60 dark:bg-gray-800 dark:border-gray-600 dark:text-white" />
                        <select value={filters.category_id ?? ''} onChange={e => applyFilter({ category_id: e.target.value })}
                            className="border rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            <option value="">All Categories</option>
                            {categories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                        </select>
                        <label className="flex items-center gap-1 text-sm cursor-pointer">
                            <input type="checkbox" checked={!!filters.low_stock} onChange={e => applyFilter({ low_stock: e.target.checked ? '1' : '' })} />
                            Low stock
                        </label>
                    </div>
                    <div className="flex gap-2">
                        <a href={route('products.export')} className="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm">Export CSV</a>
                        <Link href={route('products.create')} className="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">+ Add Product</Link>
                    </div>
                </div>

                <div className="bg-white dark:bg-gray-800 rounded-xl shadow overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead className="bg-gray-50 dark:bg-gray-700">
                            <tr className="text-left text-gray-500 dark:text-gray-400">
                                <th className="px-4 py-3">Name</th><th className="px-4 py-3">SKU</th>
                                <th className="px-4 py-3">Category</th><th className="px-4 py-3">Sale Price</th>
                                <th className="px-4 py-3">GST</th><th className="px-4 py-3">Stock</th>
                                <th className="px-4 py-3">Status</th><th className="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {products.data.map(p => (
                                <tr key={p.id} className="border-t border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750">
                                    <td className="px-4 py-3 font-medium text-gray-800 dark:text-gray-200">{p.name}</td>
                                    <td className="px-4 py-3 text-gray-500">{p.sku ?? '-'}</td>
                                    <td className="px-4 py-3 text-gray-500">{p.category?.name ?? '-'}</td>
                                    <td className="px-4 py-3">₹{Number(p.sale_price).toFixed(2)}</td>
                                    <td className="px-4 py-3">{p.gst_rate}%</td>
                                    <td className={`px-4 py-3 font-semibold ${p.stock_qty <= p.low_stock_threshold ? 'text-red-600' : 'text-green-600'}`}>
                                        {p.stock_qty} {p.unit?.symbol}
                                    </td>
                                    <td className="px-4 py-3">
                                        <span className={`px-2 py-0.5 rounded-full text-xs ${p.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'}`}>
                                            {p.is_active ? 'Active' : 'Inactive'}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3">
                                        <Link href={route('products.edit', p.id)} className="text-indigo-600 hover:underline text-sm mr-3">Edit</Link>
                                        <Link href={route('products.destroy', p.id)} method="delete" as="button"
                                            className="text-red-500 hover:underline text-sm"
                                            onClick={e => !confirm('Delete product?') && e.preventDefault()}>Delete</Link>
                                    </td>
                                </tr>
                            ))}
                            {products.data.length === 0 && (
                                <tr><td colSpan={8} className="px-4 py-8 text-center text-gray-400">No products found.</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                <div className="flex gap-1">
                    {products.links.map((l: any, i: number) => (
                        <Link key={i} href={l.url ?? '#'} preserveScroll
                            className={`px-3 py-1 rounded text-sm border ${l.active ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-300 hover:bg-gray-50'} ${!l.url ? 'opacity-40 pointer-events-none' : ''}`}
                            dangerouslySetInnerHTML={{ __html: l.label }} />
                    ))}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
