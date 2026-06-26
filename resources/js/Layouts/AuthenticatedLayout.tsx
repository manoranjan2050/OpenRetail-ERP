import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode, useState, useEffect } from 'react';
import Dropdown from '@/Components/Dropdown';

const nav = [
    { label: 'Dashboard',   route: 'dashboard',        icon: '📊', pattern: 'dashboard' },
    { label: 'New Invoice', route: 'invoices.create',  icon: '⚡', pattern: 'invoices.create' },
    { label: 'Invoices',    route: 'invoices.index',   icon: '🧾', pattern: 'invoices.index' },
    { label: 'Products',    route: 'products.index',   icon: '📦', pattern: 'products.*' },
    { label: 'Customers',   route: 'customers.index',  icon: '👥', pattern: 'customers.*' },
    { label: 'Categories',  route: 'categories.index', icon: '🗂️', pattern: 'categories.*' },
    { label: 'Units',       route: 'units.index',      icon: '⚖️', pattern: 'units.*' },
    { label: 'Settings',    route: 'settings.index',   icon: '⚙️', pattern: 'settings.*' },
    { label: 'Audit Log',   route: 'audit-log.index',  icon: '📋', pattern: 'audit-log.*' },
];

const sectionDividers: Record<number, string> = {
    0: 'OVERVIEW',
    1: 'SALES',
    3: 'INVENTORY',
    7: 'SYSTEM',
};

function NavItem({ item, active }: { item: typeof nav[0]; active: boolean }) {
    return (
        <Link
            href={route(item.route)}
            className={`group flex items-center gap-3 px-4 py-2.5 mx-2 rounded-xl text-sm font-medium transition-all duration-200 relative overflow-hidden ${
                active
                    ? 'bg-gradient-to-r from-indigo-600 to-indigo-500 text-white shadow-lg shadow-indigo-900/40'
                    : 'text-slate-400 hover:text-white hover:bg-white/8'
            }`}
            style={!active ? {} : {}}
        >
            <span className="text-base transition-transform duration-200 group-hover:scale-110">{item.icon}</span>
            <span>{item.label}</span>
            {active && <div className="ml-auto w-1.5 h-1.5 rounded-full bg-white/80" />}
        </Link>
    );
}

export default function AuthenticatedLayout({ header, children }: PropsWithChildren<{ header?: ReactNode }>) {
    const { auth } = usePage().props as any;
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [scrolled, setScrolled] = useState(false);

    useEffect(() => {
        const onScroll = () => setScrolled(window.scrollY > 8);
        window.addEventListener('scroll', onScroll, { passive: true });
        return () => window.removeEventListener('scroll', onScroll);
    }, []);

    const isActive = (pattern: string | null) => {
        if (!pattern) return false;
        try { return !!route().current(pattern); } catch { return false; }
    };

    const user = auth?.user;
    const initials = user?.name?.split(' ').map((n: string) => n[0]).join('').slice(0, 2).toUpperCase() ?? '?';

    return (
        <div className="min-h-screen bg-slate-50 dark:bg-slate-900 flex">

            {/* ── Sidebar ──────────────────────────────── */}
            <aside className={`
                fixed inset-y-0 left-0 z-50 w-60 flex flex-col
                bg-[#0f172a] border-r border-white/5 shadow-2xl
                transform transition-transform duration-300 ease-in-out
                ${sidebarOpen ? 'translate-x-0' : '-translate-x-full'} lg:translate-x-0 lg:static lg:flex
            `}>
                {/* Brand */}
                <div className="px-5 py-5 border-b border-white/10">
                    <Link href={route('dashboard')} className="flex items-center gap-3 group">
                        <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg group-hover:scale-105 transition-transform">
                            <span className="text-white font-bold text-sm">OR</span>
                        </div>
                        <div>
                            <div className="text-white font-bold text-sm leading-tight tracking-tight">OpenRetail</div>
                            <div className="text-slate-500 text-xs">ERP System</div>
                        </div>
                    </Link>
                </div>

                {/* Nav */}
                <nav className="flex-1 overflow-y-auto py-3 space-y-0.5 scrollbar-thin">
                    {nav.map((item, idx) => (
                        <div key={idx}>
                            {sectionDividers[idx] !== undefined && (
                                <div className="px-6 pt-4 pb-1">
                                    <span className="text-[10px] font-bold text-slate-600 tracking-widest uppercase">{sectionDividers[idx]}</span>
                                </div>
                            )}
                            <NavItem item={item} active={isActive(item.pattern)} />
                        </div>
                    ))}
                </nav>

                {/* User info at bottom */}
                <div className="p-4 border-t border-white/10 bg-white/3">
                    <div className="flex items-center gap-3">
                        <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white text-xs font-bold shrink-0">
                            {initials}
                        </div>
                        <div className="min-w-0 flex-1">
                            <div className="text-white text-xs font-semibold truncate">{user?.name}</div>
                            <div className="text-slate-500 text-[11px] truncate">{user?.email}</div>
                        </div>
                        <Dropdown>
                            <Dropdown.Trigger>
                                <button className="p-1 rounded-lg text-slate-500 hover:text-slate-300 hover:bg-white/10 transition-colors">
                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 5v.01M12 12v.01M12 19v.01" />
                                    </svg>
                                </button>
                            </Dropdown.Trigger>
                            <Dropdown.Content align="left">
                                <Dropdown.Link href={route('profile.edit')}>👤 Profile</Dropdown.Link>
                                <Dropdown.Link href={route('settings.index')}>⚙️ Settings</Dropdown.Link>
                                <Dropdown.Link href={route('logout')} method="post" as="button">🚪 Log Out</Dropdown.Link>
                            </Dropdown.Content>
                        </Dropdown>
                    </div>
                </div>
            </aside>

            {/* Mobile overlay */}
            {sidebarOpen && (
                <div className="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"
                    onClick={() => setSidebarOpen(false)} />
            )}

            {/* ── Main ─────────────────────────────────── */}
            <div className="flex-1 flex flex-col min-w-0 min-h-screen overflow-hidden">

                {/* Top Bar */}
                <header className={`
                    sticky top-0 z-30 flex items-center justify-between px-4 sm:px-6 h-14
                    bg-white/90 dark:bg-slate-800/90 backdrop-blur-md
                    border-b border-slate-200 dark:border-slate-700
                    transition-shadow duration-200
                    ${scrolled ? 'shadow-sm' : ''}
                `}>
                    <div className="flex items-center gap-3">
                        <button onClick={() => setSidebarOpen(true)}
                            className="lg:hidden p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-colors">
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        {header && (
                            <h1 className="text-slate-700 dark:text-slate-200 font-semibold text-base">{header}</h1>
                        )}
                    </div>

                    <div className="flex items-center gap-2 sm:gap-3">
                        <Link href={route('invoices.create')}
                            className="hidden sm:flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold px-3 py-1.5 rounded-lg transition-colors shadow-sm">
                            <span>⚡</span> New Invoice
                        </Link>
                        <Dropdown>
                            <Dropdown.Trigger>
                                <button className="flex items-center gap-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 px-2 py-1.5 transition-colors">
                                    <div className="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold">
                                        {initials}
                                    </div>
                                    <span className="hidden sm:block text-sm font-medium text-slate-700 dark:text-slate-200 max-w-[120px] truncate">{user?.name}</span>
                                    <svg className="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </Dropdown.Trigger>
                            <Dropdown.Content>
                                <Dropdown.Link href={route('profile.edit')}>👤 Profile</Dropdown.Link>
                                <Dropdown.Link href={route('settings.index')}>⚙️ Settings</Dropdown.Link>
                                <Dropdown.Link href={route('audit-log.index')}>📋 Audit Log</Dropdown.Link>
                                <Dropdown.Link href={route('logout')} method="post" as="button">🚪 Log Out</Dropdown.Link>
                            </Dropdown.Content>
                        </Dropdown>
                    </div>
                </header>

                {/* Content */}
                <main className="flex-1">{children}</main>

                {/* Footer */}
                <footer className="border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/50 px-6 py-3">
                    <div className="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-400">
                        <span>© {new Date().getFullYear()} <span className="text-indigo-500 font-medium">OpenRetail ERP</span> · Open Source · All rights reserved</span>
                        <div className="flex items-center gap-4">
                            <Link href={route('settings.index')} className="hover:text-indigo-500 transition-colors">Settings</Link>
                            <Link href={route('audit-log.index')} className="hover:text-indigo-500 transition-colors">Audit Log</Link>
                            <span className="bg-slate-100 dark:bg-slate-700 text-slate-500 px-2 py-0.5 rounded text-[11px] font-mono">v1.0.0</span>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    );
}
