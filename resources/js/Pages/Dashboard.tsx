import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend } from 'chart.js';
import { Bar } from 'react-chartjs-2';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

interface Stats { totalProducts: number; totalCustomers: number; lowStockCount: number; totalSalesToday: number; outstandingCredit: number }
interface MonthlySale { month: string; total: number }
interface TopProduct { product_name: string; total_qty: number; total_revenue: number }
interface SalesByMode { payment_mode: string; total: number }
interface LowStockItem { id: number; name: string; stock_qty: number; low_stock_threshold: number; unit?: { symbol: string } }

const fmt = (n: number) => '₹' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2 });
const fmtShort = (n: number) => n >= 100000 ? '₹' + (n / 100000).toFixed(1) + 'L' : n >= 1000 ? '₹' + (n / 1000).toFixed(1) + 'K' : '₹' + n.toFixed(0);
const MODE_COLORS: Record<string, string> = { cash: '#10b981', upi: '#6366f1', card: '#f59e0b', credit: '#ef4444', mixed: '#8b5cf6' };
const MODE_ICONS: Record<string, string> = { cash: '💵', upi: '📱', card: '💳', credit: '📒', mixed: '🔀' };

function StatCard({ label, value, icon, bg, sub }: { label: string; value: string; icon: string; bg: string; sub?: string }) {
    return (
        <div className="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-md transition-all duration-200 group">
            <div className="flex items-start justify-between mb-3">
                <div className={`w-11 h-11 rounded-xl ${bg} flex items-center justify-center text-xl group-hover:scale-110 transition-transform duration-200`}>{icon}</div>
                <span className="text-xs text-slate-400 font-medium">{label}</span>
            </div>
            <div className="text-2xl font-bold text-slate-800 dark:text-white">{value}</div>
            {sub && <div className="text-xs text-slate-400 mt-1">{sub}</div>}
        </div>
    );
}

export default function Dashboard({ stats, monthlySales, topProducts, salesByMode, lowStockItems }: {
    stats: Stats; monthlySales: MonthlySale[]; topProducts: TopProduct[]; salesByMode: SalesByMode[]; lowStockItems: LowStockItem[]
}) {
    const months = monthlySales.map(m => {
        const [y, mo] = m.month.split('-');
        return new Date(+y, +mo - 1).toLocaleString('en-IN', { month: 'short', year: '2-digit' });
    });
    const totals = monthlySales.map(m => Number(m.total));
    const totalByMode = salesByMode.reduce((s, x) => s + Number(x.total), 0);

    const barData = {
        labels: months,
        datasets: [{
            label: 'Sales (₹)', data: totals,
            backgroundColor: 'rgba(99,102,241,0.15)', borderColor: '#6366f1',
            borderWidth: 2.5, borderRadius: 8, hoverBackgroundColor: 'rgba(99,102,241,0.3)',
        }],
    };
    const barOpts: any = {
        responsive: true, maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: (ctx: any) => ' ' + fmt(ctx.parsed.y) }, backgroundColor: '#0f172a', titleColor: '#94a3b8', bodyColor: '#fff', padding: 12, cornerRadius: 10 },
        },
        scales: {
            x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 11 } } },
            y: { grid: { color: 'rgba(148,163,184,0.08)' }, ticks: { color: '#94a3b8', font: { size: 11 }, callback: (v: any) => fmtShort(v) } },
        },
    };

    return (
        <AuthenticatedLayout header={<span>Dashboard</span>}>
            <Head title="Dashboard" />
            <div className="p-4 sm:p-6 max-w-7xl mx-auto space-y-6">

                {/* Stat cards */}
                <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                    <StatCard label="Products" value={stats.totalProducts.toString()} icon="📦" bg="bg-blue-50" />
                    <StatCard label="Customers" value={stats.totalCustomers.toString()} icon="👥" bg="bg-purple-50" />
                    <StatCard label="Low Stock" value={stats.lowStockCount.toString()} icon="⚠️" bg="bg-orange-50" sub={stats.lowStockCount > 0 ? 'needs reorder' : 'all good'} />
                    <StatCard label="Today Sales" value={fmtShort(stats.totalSalesToday)} icon="💰" bg="bg-green-50" sub={fmt(stats.totalSalesToday)} />
                    <StatCard label="Outstanding" value={fmtShort(stats.outstandingCredit)} icon="📒" bg="bg-red-50" sub="credit balance" />
                </div>

                {/* Charts row */}
                <div className="grid lg:grid-cols-3 gap-4">
                    <div className="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm">
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="font-semibold text-slate-700 dark:text-slate-200">Monthly Sales</h3>
                            <span className="text-xs bg-indigo-50 text-indigo-600 px-2.5 py-1 rounded-full font-medium">Last 12 months</span>
                        </div>
                        <div style={{ height: 220 }}>
                            {totals.length > 0
                                ? <Bar data={barData} options={barOpts} />
                                : <div className="h-full flex flex-col items-center justify-center text-slate-300 gap-2"><span className="text-4xl">📊</span><span className="text-sm">No sales yet</span></div>
                            }
                        </div>
                    </div>

                    <div className="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm">
                        <h3 className="font-semibold text-slate-700 dark:text-slate-200 mb-4">Sales by Mode</h3>
                        <div className="space-y-3">
                            {salesByMode.length > 0 ? salesByMode.map(s => {
                                const pct = totalByMode > 0 ? (Number(s.total) / totalByMode) * 100 : 0;
                                return (
                                    <div key={s.payment_mode}>
                                        <div className="flex items-center justify-between text-sm mb-1.5">
                                            <span className="text-slate-600 dark:text-slate-300 flex items-center gap-1.5 font-medium capitalize">
                                                {MODE_ICONS[s.payment_mode] ?? '💳'} {s.payment_mode}
                                            </span>
                                            <span className="font-bold text-slate-700 dark:text-slate-200">{fmtShort(Number(s.total))}</span>
                                        </div>
                                        <div className="h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                                            <div className="h-full rounded-full transition-all duration-700" style={{ width: `${pct}%`, backgroundColor: MODE_COLORS[s.payment_mode] ?? '#6366f1' }} />
                                        </div>
                                    </div>
                                );
                            }) : <div className="text-center py-10 text-slate-300 text-sm">No sales yet</div>}
                        </div>
                    </div>
                </div>

                {/* Tables row */}
                <div className="grid lg:grid-cols-2 gap-4">
                    <div className="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                        <div className="px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                            <h3 className="font-semibold text-slate-700 dark:text-slate-200">🏆 Top Products</h3>
                            <Link href={route('products.index')} className="text-xs text-indigo-500 hover:text-indigo-600 font-medium">View all →</Link>
                        </div>
                        <table className="w-full text-sm">
                            <thead><tr className="text-xs text-slate-400 uppercase tracking-wide bg-slate-50 dark:bg-slate-700/40">
                                <th className="px-5 py-2.5 text-left">#</th>
                                <th className="px-5 py-2.5 text-left">Product</th>
                                <th className="px-5 py-2.5 text-right">Qty</th>
                                <th className="px-5 py-2.5 text-right">Revenue</th>
                            </tr></thead>
                            <tbody>{topProducts.map((p, i) => (
                                <tr key={i} className="border-t border-slate-50 dark:border-slate-700 hover:bg-slate-50/50 transition-colors">
                                    <td className="px-5 py-3">
                                        <span className="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-600 text-xs font-bold flex items-center justify-center">{i + 1}</span>
                                    </td>
                                    <td className="px-5 py-3 font-medium text-slate-700 dark:text-slate-200 truncate max-w-[130px]">{p.product_name}</td>
                                    <td className="px-5 py-3 text-right text-slate-500">{Number(p.total_qty).toFixed(1)}</td>
                                    <td className="px-5 py-3 text-right font-bold text-slate-800 dark:text-white">{fmtShort(Number(p.total_revenue))}</td>
                                </tr>
                            ))}
                            {topProducts.length === 0 && <tr><td colSpan={4} className="px-5 py-8 text-center text-slate-300 text-sm">No sales yet</td></tr>}
                            </tbody>
                        </table>
                    </div>

                    <div className="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                        <div className="px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                            <h3 className="font-semibold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                                ⚠️ Low Stock
                                {lowStockItems.length > 0 && <span className="bg-red-100 text-red-600 text-xs font-bold px-2 py-0.5 rounded-full animate-pulse">{lowStockItems.length}</span>}
                            </h3>
                            <Link href={route('products.index')} className="text-xs text-indigo-500 hover:text-indigo-600 font-medium">Manage →</Link>
                        </div>
                        <div className="divide-y divide-slate-50 dark:divide-slate-700">
                            {lowStockItems.map(item => (
                                <div key={item.id} className="flex items-center justify-between px-5 py-3 hover:bg-slate-50/50 transition-colors">
                                    <div>
                                        <div className="font-medium text-slate-700 dark:text-slate-200 text-sm">{item.name}</div>
                                        <div className="text-xs text-slate-400 mt-0.5">Min: {item.low_stock_threshold} {item.unit?.symbol}</div>
                                    </div>
                                    <div className="text-right">
                                        <div className={`text-sm font-bold ${Number(item.stock_qty) <= 0 ? 'text-red-500' : 'text-orange-500'}`}>{item.stock_qty} {item.unit?.symbol}</div>
                                        <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${Number(item.stock_qty) <= 0 ? 'bg-red-100 text-red-600' : 'bg-orange-100 text-orange-600'}`}>
                                            {Number(item.stock_qty) <= 0 ? 'Out' : 'Low'}
                                        </span>
                                    </div>
                                </div>
                            ))}
                            {lowStockItems.length === 0 && (
                                <div className="px-5 py-10 text-center">
                                    <div className="text-4xl mb-2">✅</div>
                                    <div className="text-slate-400 text-sm font-medium">All products well stocked</div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
