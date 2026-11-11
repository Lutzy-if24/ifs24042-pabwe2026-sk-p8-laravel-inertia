import { Link, router, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';

export default function AppLayout({ children, title }) {
    const { auth, flash } = usePage().props;
    const user = auth?.user;
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [flashMsg, setFlashMsg] = useState(null);

    useEffect(() => {
        if (flash?.success || flash?.error) {
            setFlashMsg({ type: flash.success ? 'success' : 'error', message: flash.success || flash.error });
            const timer = setTimeout(() => setFlashMsg(null), 4000);
            return () => clearTimeout(timer);
        }
    }, [flash]);

    const isAdmin = user?.role === 'admin';

    const navLinks = isAdmin ? [
        { href: '/admin/users', label: 'Kelola Pengguna', icon: '👥' },
        { href: '/admin/posts', label: 'Semua Artikel', icon: '📋' },
    ] : [
        { href: '/dashboard/posts', label: 'Artikel Saya', icon: '📝' },
    ];

    return (
        <div className="min-h-screen bg-slate-50 flex">
            {sidebarOpen && (
                <div className="fixed inset-0 bg-black/40 z-20 lg:hidden" onClick={() => setSidebarOpen(false)} />
            )}

            <aside className={`fixed top-0 left-0 h-full w-64 bg-gradient-to-b from-violet-900 to-violet-800 text-white z-30 transform transition-transform duration-300 ${sidebarOpen ? 'translate-x-0' : '-translate-x-full'} lg:translate-x-0 lg:static lg:flex lg:flex-col`}>
                <div className="p-6 border-b border-violet-700">
                    <Link href="/" className="flex items-center gap-3">
                        <div className="w-9 h-9 bg-white rounded-lg flex items-center justify-center">
                            <span className="text-violet-700 font-black text-lg">B</span>
                        </div>
                        <div>
                            <div className="font-bold text-sm leading-tight">BlogVerse Pro</div>
                            <div className="text-violet-300 text-xs">{isAdmin ? 'Panel Admin' : 'Dashboard Penulis'}</div>
                        </div>
                    </Link>
                </div>

                <nav className="flex-1 p-4 space-y-1">
                    {navLinks.map(link => (
                        <Link
                            key={link.href}
                            href={link.href}
                            className="flex items-center gap-3 px-3 py-2.5 rounded-lg text-violet-100 hover:bg-violet-700 hover:text-white transition-all text-sm font-medium"
                        >
                            <span>{link.icon}</span>
                            {link.label}
                        </Link>
                    ))}
                </nav>

                <div className="p-4 border-t border-violet-700">
                    <div className="flex items-center gap-3 mb-3">
                        {user?.profile_photo_url ? (
                            <img src={user.profile_photo_url} alt={user.name} className="w-9 h-9 rounded-full object-cover" />
                        ) : (
                            <div className="w-9 h-9 rounded-full bg-violet-600 flex items-center justify-center font-bold text-sm">
                                {user?.name?.charAt(0).toUpperCase()}
                            </div>
                        )}
                        <div className="min-w-0">
                            <div className="text-sm font-semibold truncate">{user?.name}</div>
                            <div className="text-violet-300 text-xs">@{user?.username}</div>
                        </div>
                    </div>
                    <div className="space-y-1">
                        <Link href="/profile" className="flex items-center gap-2 px-3 py-2 rounded-lg text-violet-200 hover:bg-violet-700 text-xs transition-all">
                            ⚙️ Edit Profil
                        </Link>
                        <Link href="/" className="flex items-center gap-2 px-3 py-2 rounded-lg text-violet-200 hover:bg-violet-700 text-xs transition-all">
                            🌐 Lihat Blog
                        </Link>
                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            className="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-red-300 hover:bg-red-900/40 text-xs transition-all cursor-pointer"
                        >
                            🚪 Keluar
                        </Link>
                    </div>
                </div>
            </aside>

            <div className="flex-1 flex flex-col min-w-0">
                <header className="bg-white border-b border-slate-200 px-4 py-3 flex items-center gap-4 sticky top-0 z-10">
                    <button
                        onClick={() => setSidebarOpen(!sidebarOpen)}
                        className="lg:hidden p-2 rounded-lg hover:bg-slate-100 cursor-pointer"
                    >
                        ☰
                    </button>
                    <h1 className="font-semibold text-slate-800 flex-1">{title || 'Dashboard'}</h1>
                </header>

                {flashMsg && (
                    <div className={`mx-6 mt-4 px-4 py-3 rounded-lg text-sm font-medium ${
                        flashMsg.type === 'success'
                            ? 'bg-emerald-50 text-emerald-800 border border-emerald-200'
                            : 'bg-red-50 text-red-800 border border-red-200'
                    }`}>
                        {flashMsg.type === 'success' ? '✅ ' : '❌ '}{flashMsg.message}
                    </div>
                )}

                <main className="flex-1 p-6">
                    {children}
                </main>
            </div>
        </div>
    );
}
