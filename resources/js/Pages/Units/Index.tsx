import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function UnitsIndex({ units }: { units: any[] }) {
    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800 dark:text-gray-200">Units of Measure</h2>}>
            <Head title="Units" />
            <div className="py-6 px-4 max-w-2xl mx-auto space-y-4">
                <div className="flex justify-end"><Link href={route('units.create')} className="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">+ Add Unit</Link></div>
                <div className="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
                    <table className="w-full text-sm"><thead className="bg-gray-50 dark:bg-gray-700"><tr className="text-left text-gray-500 dark:text-gray-400"><th className="px-4 py-3">Name</th><th className="px-4 py-3">Symbol</th><th className="px-4 py-3">Actions</th></tr></thead>
                        <tbody>{units.map(u => (<tr key={u.id} className="border-t border-gray-100 dark:border-gray-700"><td className="px-4 py-3 font-medium text-gray-800 dark:text-gray-200">{u.name}</td><td className="px-4 py-3 text-gray-500">{u.symbol}</td><td className="px-4 py-3 flex gap-2"><Link href={route('units.edit', u.id)} className="text-indigo-600 hover:underline text-sm">Edit</Link><Link href={route('units.destroy', u.id)} method="delete" as="button" className="text-red-500 hover:underline text-sm" onClick={e => !confirm('Delete?') && e.preventDefault()}>Delete</Link></td></tr>))}</tbody>
                    </table>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
