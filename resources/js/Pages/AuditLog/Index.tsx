import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

interface Log { id: number; log_name: string; description: string; created_at: string; causer?: { name: string }; properties?: any }

export default function AuditLogIndex({ logs, filters }: { logs: { data: Log[]; links: any[] }; filters: any }) {
    const [from, setFrom] = useState(filters.from ?? '');
    const [to, setTo] = useState(filters.to ?? '');

    const apply = () => router.get(route('audit-log.index'), { ...filters, from, to }, { preserveState: true });

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800 dark:text-gray-200">Audit Log</h2>}>
            <Head title="Audit Log" />
            <div className="py-6 px-4 max-w-7xl mx-auto space-y-4">
                <div className="flex flex-wrap gap-3">
                    <input type="date" value={from} onChange={e => setFrom(e.target.value)} className="border rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" />
                    <input type="date" value={to} onChange={e => setTo(e.target.value)} className="border rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" />
                    <button onClick={apply} className="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">Filter</button>
                    <Link href={route('audit-log.index')} className="text-gray-500 text-sm px-3 py-2">Clear</Link>
                </div>

                <div className="bg-white dark:bg-gray-800 rounded-xl shadow overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead className="bg-gray-50 dark:bg-gray-700">
                            <tr className="text-left text-gray-500 dark:text-gray-400">
                                <th className="px-4 py-3">Date/Time</th><th className="px-4 py-3">User</th>
                                <th className="px-4 py-3">Module</th><th className="px-4 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {logs.data.map(log => (
                                <tr key={log.id} className="border-t border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750">
                                    <td className="px-4 py-3 text-gray-500 whitespace-nowrap">{new Date(log.created_at).toLocaleString('en-IN')}</td>
                                    <td className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">{log.causer?.name ?? 'System'}</td>
                                    <td className="px-4 py-3"><span className="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-xs">{log.log_name}</span></td>
                                    <td className="px-4 py-3 text-gray-700 dark:text-gray-300">{log.description}</td>
                                </tr>
                            ))}
                            {logs.data.length === 0 && (
                                <tr><td colSpan={4} className="px-4 py-8 text-center text-gray-400">No activity log entries found.</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
                <div className="flex gap-1">
                    {logs.links.map((l: any, i: number) => (
                        <Link key={i} href={l.url ?? '#'} className={`px-3 py-1 rounded text-sm border ${l.active ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-300 hover:bg-gray-50'} ${!l.url ? 'opacity-40 pointer-events-none' : ''}`} dangerouslySetInnerHTML={{ __html: l.label }} />
                    ))}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
