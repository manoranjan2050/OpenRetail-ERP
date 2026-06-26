import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

interface Stat {
    totalProducts: number;
    totalCustomers: number;
    lowStockCount: number;
    totalSalesToday: number;
    outstandingCredit: number;
}
interface MonthlySale { month: string; total: number }
interface TopProduct { product_name: string; total_qty: number; total_revenue: number }
interface SalesByMode { payment_mode: string; total: number }
interface LowStockItem { id: number; name: string; stock_qty: number; low_stock_threshold: number; unit?: { symbol: string } }

export default function Dashboard({ stats, monthlySales, topProducts, salesByMode, lowStockItems }: {
    stats: Stat;
    monthlySales: MonthlySale[];
    topProducts: TopProduct[];
    salesByMode: SalesByMode[];
    lowStockItems: LowStockItem[];
}) {
    const fmt = (n: number) => '₹' + n.toLocaleString('en-IN', { minimumFractionDigits: 2 });

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800 dark:text-gray-200">Dashboard</h2>}>
            <Head title="Dashboard" />
            <div className="py-8 px-4 max-w-7xl mx-auto space-y-6">

                {/* Stat tiles */}
                <div className="grid grid-cols-2 lg:grid-cols-5 gap-4">
                    {[
                        { label: 'Products', value: stats.totalProducts, color: 'bg-blue-600', href: route('products.index') },
                        { label: 'Customers', value: stats.totalCustomers, color: 'bg-green-600', href: route('customers.index') },
                        { label: 'Low Stock', value: stats.lowStockCount, color: 'bg-yellow-500', href: route('products.index') + '?low_stock=1' },
                        { label: "Today's Sales", value: fmt(stats.totalSalesToday), color: 'bg-indigo-600', href: route('invoices.index') },
                        { label: 'Outstanding Credit', value: fmt(stats.outstandingCredit), color: 'bg-red-600', href: route('customers.index') },
                    ].map(tile => (
                        <Link key={tile.label} href={tile.href}
                            className={`${tile.color} text-white rounded-xl p-5 shadow hover:opacity-90 transition`}>
                            <div className="text-sm font-medium opacity-80">{tile.label}</div>
                            <div className="text-2xl font-bold mt-1">{tile.value}</div>
                        </Link>
                    ))}
                </div>

                <div className="grid lg:grid-cols-3 gap-6">
                    {/* Monthly sales chart (simple bars) */}
                    <div className="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 className="text-base font-semibold mb-4 text-gray-700 dark:text-gray-300">Monthly Sales (Last 12 Months)</h3>
                        <div className="flex items-end gap-1 h-40">
                            {monthlySales.map(m => {
                                const max = Math.max(...monthlySales.map(x => x.total), 1);
                                const pct = (m.total / max) * 100;
                                return (
                                    <div key={m.month} className="flex-1 flex flex-col items-center gap-1">
                                        <div className="text-xs text-gray-500">{fmt(m.total).replace('₹','').split('.')[0]}</div>
                                        <div className="w-full bg-indigo-500 rounded-t" style={{ height: `${Math.max(pct, 4)}%` }} title={fmt(m.total)} />
                                        <div className="text-xs text-gray-400">{m.month.slice(5)}</div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    {/* Sales by mode */}
                    <div className="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 className="text-base font-semibold mb-4 text-gray-700 dark:text-gray-300">Sales by Mode</h3>
                        <div className="space-y-2">
                            {salesByMode.map(m => (
                                <div key={m.payment_mode} className="flex justify-between text-sm">
                                    <span className="capitalize text-gray-600 dark:text-gray-400">{m.payment_mode}</span>
                                    <span className="font-semibold text-gray-800 dark:text-gray-200">{fmt(m.total)}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>

                <div className="grid lg:grid-cols-2 gap-6">
                    {/* Top products */}
                    <div className="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 className="text-base font-semibold mb-4 text-gray-700 dark:text-gray-300">Top 5 Products</h3>
                        <table className="w-full text-sm">
                            <thead><tr className="text-left text-gray-500"><th className="pb-2">Product</th><th className="pb-2">Qty</th><th className="pb-2">Revenue</th></tr></thead>
                            <tbody>
                                {topProducts.map(p => (
                                    <tr key={p.product_name} className="border-t border-gray-100 dark:border-gray-700">
                                        <td className="py-2 text-gray-700 dark:text-gray-300">{p.product_name}</td>
                                        <td className="py-2">{p.total_qty}</td>
                                        <td className="py-2 font-semibold">{fmt(p.total_revenue)}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Low stock alerts */}
                    <div className="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 className="text-base font-semibold mb-4 text-yellow-600">⚠ Low Stock Alerts</h3>
                        {lowStockItems.length === 0
                            ? <p className="text-sm text-gray-500">All products have sufficient stock.</p>
                            : <table className="w-full text-sm">
                                <thead><tr className="text-left text-gray-500"><th className="pb-2">Product</th><th className="pb-2">Stock</th><th className="pb-2">Min</th></tr></thead>
                                <tbody>
                                    {lowStockItems.map(p => (
                                        <tr key={p.id} className="border-t border-gray-100 dark:border-gray-700">
                                            <td className="py-2 text-gray-700 dark:text-gray-300">{p.name}</td>
                                            <td className="py-2 text-red-600 font-semibold">{p.stock_qty} {p.unit?.symbol}</td>
                                            <td className="py-2 text-gray-500">{p.low_stock_threshold}</td>
                                        </tr>
                                    ))}
                                </tbody>
                              </table>
                        }
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
