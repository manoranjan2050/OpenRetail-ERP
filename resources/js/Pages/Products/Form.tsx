import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

interface Product { id?: number; name: string; sku?: string; barcode?: string; category_id?: number; unit_id?: number; hsn_code?: string; purchase_price: number; sale_price: number; gst_rate: number; stock_qty: number; low_stock_threshold: number; expiry_date?: string; batch?: string; is_active: boolean }

export default function ProductForm({ product, categories, units, gstRates }: {
    product?: Product;
    categories: { id: number; name: string }[];
    units: { id: number; name: string; symbol: string }[];
    gstRates: number[];
}) {
    const { data, setData, post, put, processing, errors } = useForm({
        name: product?.name ?? '', sku: product?.sku ?? '', barcode: product?.barcode ?? '',
        category_id: product?.category_id ?? '', unit_id: product?.unit_id ?? '',
        hsn_code: product?.hsn_code ?? '', purchase_price: product?.purchase_price ?? 0,
        sale_price: product?.sale_price ?? 0, gst_rate: product?.gst_rate ?? 0,
        stock_qty: product?.stock_qty ?? 0, low_stock_threshold: product?.low_stock_threshold ?? 5,
        expiry_date: product?.expiry_date ?? '', batch: product?.batch ?? '',
        is_active: product?.is_active ?? true, image: null as any,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        if (product?.id) put(route('products.update', product.id));
        else post(route('products.store'));
    };

    const Field = ({ label, error, children }: { label: string; error?: string; children: React.ReactNode }) => (
        <div>
            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{label}</label>
            {children}
            {error && <p className="text-red-500 text-xs mt-1">{error}</p>}
        </div>
    );
    const inp = "w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500";

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800 dark:text-gray-200">{product ? 'Edit Product' : 'Add Product'}</h2>}>
            <Head title={product ? 'Edit Product' : 'Add Product'} />
            <div className="py-6 px-4 max-w-3xl mx-auto">
                <form onSubmit={submit} encType="multipart/form-data" className="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <Field label="Product Name *" error={errors.name}>
                            <input className={inp} value={data.name} onChange={e => setData('name', e.target.value)} required />
                        </Field>
                        <Field label="SKU" error={errors.sku}>
                            <input className={inp} value={data.sku} onChange={e => setData('sku', e.target.value)} />
                        </Field>
                        <Field label="Category" error={errors.category_id}>
                            <select className={inp} value={data.category_id} onChange={e => setData('category_id', e.target.value as any)}>
                                <option value="">— Select —</option>
                                {categories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                            </select>
                        </Field>
                        <Field label="Unit" error={errors.unit_id}>
                            <select className={inp} value={data.unit_id} onChange={e => setData('unit_id', e.target.value as any)}>
                                <option value="">— Select —</option>
                                {units.map(u => <option key={u.id} value={u.id}>{u.name} ({u.symbol})</option>)}
                            </select>
                        </Field>
                        <Field label="HSN Code" error={errors.hsn_code}>
                            <input className={inp} value={data.hsn_code} onChange={e => setData('hsn_code', e.target.value)} />
                        </Field>
                        <Field label="GST Rate %" error={errors.gst_rate}>
                            <select className={inp} value={data.gst_rate} onChange={e => setData('gst_rate', Number(e.target.value))}>
                                {gstRates.map(r => <option key={r} value={r}>{r}%</option>)}
                            </select>
                        </Field>
                        <Field label="Purchase Price (₹) *" error={errors.purchase_price}>
                            <input type="number" step="0.01" className={inp} value={data.purchase_price} onChange={e => setData('purchase_price', Number(e.target.value))} required />
                        </Field>
                        <Field label="Sale Price (₹) *" error={errors.sale_price}>
                            <input type="number" step="0.01" className={inp} value={data.sale_price} onChange={e => setData('sale_price', Number(e.target.value))} required />
                        </Field>
                        {!product && (
                            <Field label="Opening Stock Qty *" error={errors.stock_qty}>
                                <input type="number" step="0.001" className={inp} value={data.stock_qty} onChange={e => setData('stock_qty', Number(e.target.value))} required />
                            </Field>
                        )}
                        <Field label="Low Stock Alert Qty *" error={errors.low_stock_threshold}>
                            <input type="number" step="0.001" className={inp} value={data.low_stock_threshold} onChange={e => setData('low_stock_threshold', Number(e.target.value))} required />
                        </Field>
                        <Field label="Expiry Date" error={errors.expiry_date}>
                            <input type="date" className={inp} value={data.expiry_date} onChange={e => setData('expiry_date', e.target.value)} />
                        </Field>
                        <Field label="Batch / Lot" error={errors.batch}>
                            <input className={inp} value={data.batch} onChange={e => setData('batch', e.target.value)} />
                        </Field>
                    </div>
                    <Field label="Product Image" error={errors.image}>
                        <input type="file" accept="image/*" className={inp} onChange={e => setData('image', e.target.files?.[0] as any)} />
                    </Field>
                    <label className="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" checked={data.is_active} onChange={e => setData('is_active', e.target.checked)} />
                        <span className="text-gray-700 dark:text-gray-300">Active</span>
                    </label>
                    <div className="flex gap-3 pt-2">
                        <button type="submit" disabled={processing}
                            className="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg text-sm disabled:opacity-50">
                            {processing ? 'Saving…' : product ? 'Update Product' : 'Add Product'}
                        </button>
                        <a href={route('products.index')} className="text-gray-500 hover:text-gray-700 px-4 py-2 text-sm">Cancel</a>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
