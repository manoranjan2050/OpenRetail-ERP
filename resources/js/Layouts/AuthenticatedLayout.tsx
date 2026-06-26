import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode, useState } from 'react';
import Dropdown from '@/Components/Dropdown';

const nav = [
    { label: 'Dashboard', route: 'dashboard', icon: '📊' },
    { label: 'New Invoice', route: 'invoices.create', icon: '🧾' },
    { label: 'Invoices', route: 'invoices.index', icon: '📄' },
    { label: 'Products', route: 'products.index', icon: '📦' },
    { label: 'Customers', route: 'customers.index', icon: '👥' },
    { label: 'Categories', route: 'categories.index', icon: '🗂' },
    { label: 'Units', route: 'units.index', icon: '⚖️' },
    { label: 'Settings', route: 'settings.index', icon: '⚙️' },
    { label: 'Audit Log', route: 'audit-log.index', icon: '📋' },
];

export default function AuthenticatedLayout({ header, children }: PropsWithChildren<{ header?: ReactNode }>) {
    const { auth } = usePage().props as any;
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const currentRoute = usePage().url;

    const isActive = (routeName: string) => {
        try { return route().current(routeName) as boolean; } catch { return false; }
    };

    return (
        <div className="min-h-screen bg-gray-100 dark:bg-gray-900 flex">
            {/* Sidebar */}
            <aside className={`fixed inset-y-0 left-0 z-50 w-56 bg-gray-900 text-white flex flex-col transform transition-transform ${sidebarOpen ? 'translate-x-0' : '-translate-x-full'} lg:translate-x-0 lg:static lg:flex`}>
                <div className="p-4 border-b border-gray-700">
                    <span className="text-lg font-bold text-indigo-400">OpenRetail ERP</span>
                </div>
                <nav className="flex-1 overflow-y-auto py-3">
                    {nav.map(item => (
                        <Link key={item.route} href={route(item.route)}
                            className={`flex items-center gap-3 px-4 py-2.5 text-sm transition-colors ${isActive(item.route) ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'}`}>
                            <span>{item.icon}</span>
                            <span>{item.label}</span>
                        </Link>
                    ))}
                </nav>
                <div className="p-4 border-t border-gray-700 text-xs text-gray-500">
                    Logged in as <span className="text-gray-300">{auth?.user?.name}</span>
                </div>
            </aside>

            {/* Overlay */}
            {sidebarOpen && <div className="fixed inset-0 z-40 bg-black/50 lg:hidden" onClick={() => setSidebarOpen(false)} />}

            {/* Main */}
            <div className="flex-1 flex flex-col min-w-0">
                {/* Top bar */}
                <header className="bg-white dark:bg-gray-800 shadow-sm z-30 flex items-center justify-between px-4 h-14">
                    <div className="flex items-center gap-3">
                        <button onClick={() => setSidebarOpen(!sidebarOpen)} className="lg:hidden text-gray-500 hover:text-gray-700 p-1">
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" /></svg>
                        </button>
                        {header && <div className="text-gray-800 dark:text-gray-200">{header}</div>}
                    </div>
                    <Dropdown>
                        <Dropdown.Trigger>
                            <button className="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white">
                                <div className="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white font-semibold text-xs">
                                    {auth?.user?.name?.[0]?.toUpperCase()}
                                </div>
                                <span className="hidden sm:block">{auth?.user?.name}</span>
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" /></svg>
                            </button>
                        </Dropdown.Trigger>
                        <Dropdown.Content>
                            <Dropdown.Link href={route('profile.edit')}>Profile</Dropdown.Link>
                            <Dropdown.Link href={route('logout')} method="post" as="button">Log Out</Dropdown.Link>
                        </Dropdown.Content>
                    </Dropdown>
                </header>

                <main className="flex-1 overflow-y-auto">{children}</main>
            </div>
        </div>
    );
}
