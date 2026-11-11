import { Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';

function PublicNavbar() {
    const { auth } = usePage().props;
    const user = auth?.user;
    const [menuOpen, setMenuOpen] = useState(false);

    return (
        <nav className="sticky top-0 z-50 bg-white/80 backdrop-blur-xl border-b border-slate-200 shadow-sm">
            <div className="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
                <Link href="/" className="flex items-center gap-2.5 font-black text-violet-700 text-xl">
                    <div className="w-8 h-8 bg-violet-700 rounded-lg flex items-center justify-center">
                        <span className="text-white font-black text-sm">B</span>
                    </div>
                    BlogVerse Pro
                </Link>

                <div className="hidden md:flex items-center gap-6">
                    <Link href="/" className="text-slate-600 hover:text-violet-700 font-medium text-sm transition-colors">Beranda</Link>
                    <Link href="/articles" className="text-slate-600 hover:text-violet-700 font-medium text-sm transition-colors">Artikel</Link>
                </div>

                <div className="flex items-center gap-3">
                    {user ? (
                        <>
                            <Link
                                href={user.role === 'admin' ? '/admin/users' : '/dashboard/posts'}
                                className="hidden md:flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-all cursor-pointer"
                            >
                                {user.role === 'admin' ? '🛡️ Panel Admin' : '✍️ Dashboard Penulis'}
                            </Link>
                            <div className="relative">
                                <button onClick={() => setMenuOpen(!menuOpen)} className="flex items-center gap-2 cursor-pointer">
                                    {user.profile_photo_url ? (
                                        <img src={user.profile_photo_url} alt={user.name} className="w-8 h-8 rounded-full object-cover" />
                                    ) : (
                                        <div className="w-8 h-8 rounded-full bg-violet-600 flex items-center justify-center text-white font-bold text-sm">
                                            {user.name?.charAt(0).toUpperCase()}
                                        </div>
                                    )}
                                </button>
                                {menuOpen && (
                                    <div className="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 py-2 z-50">
                                        <div className="px-4 py-2 border-b border-slate-100">
                                            <p className="text-sm font-semibold text-slate-800">{user.name}</p>
                                            <p className="text-xs text-slate-500">@{user.username}</p>
                                        </div>
                                        <Link href={user.role === 'admin' ? '/admin/users' : '/dashboard/posts'}
                                            className="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                            {user.role === 'admin' ? '🛡️ Panel Admin' : '📝 Dashboard'}
                                        </Link>
                                        <Link href="/profile" className="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">⚙️ Profil</Link>
                                        <Link href="/logout" method="post" as="button"
                                            className="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 cursor-pointer">
                                            🚪 Keluar
                                        </Link>
                                    </div>
                                )}
                            </div>
                        </>
                    ) : (
                        <>
                            <Link href="/login" className="flex items-center gap-1.5 text-slate-700 hover:text-violet-700 font-medium text-sm transition-colors px-3 py-2 rounded-lg hover:bg-violet-50 cursor-pointer">
                                🔐 Masuk
                            </Link>
                            <Link href="/register" className="flex items-center gap-1.5 bg-violet-600 hover:bg-violet-700 text-white font-medium text-sm px-4 py-2 rounded-lg transition-all cursor-pointer">
                                ✨ Daftar
                            </Link>
                        </>
                    )}
                </div>
            </div>
        </nav>
    );
}

function PostCard({ post }) {
    const categoryColors = {
        'Teknologi': 'bg-blue-100 text-blue-700',
        'Pendidikan': 'bg-emerald-100 text-emerald-700',
        'Gaya Hidup': 'bg-orange-100 text-orange-700',
        'Bisnis': 'bg-amber-100 text-amber-700',
        'Opini': 'bg-purple-100 text-purple-700',
        'Umum': 'bg-slate-100 text-slate-700',
    };

    return (
        <article className="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md border border-slate-100 transition-all duration-300 hover:-translate-y-0.5 group">
            {post.cover_image_path ? (
                <div className="aspect-video overflow-hidden">
                    <img
                        src={`/storage/${post.cover_image_path}`}
                        alt={post.title}
                        className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    />
                </div>
            ) : (
                <div className="aspect-video bg-gradient-to-br from-violet-100 to-indigo-100 flex items-center justify-center">
                    <span className="text-5xl opacity-30">📝</span>
                </div>
            )}
            <div className="p-5">
                <div className="flex items-center gap-2 mb-3">
                    <span className={`text-xs font-semibold px-2.5 py-1 rounded-full ${categoryColors[post.category] || 'bg-slate-100 text-slate-700'}`}>
                        {post.category}
                    </span>
                </div>
                <h2 className="font-bold text-slate-900 text-lg leading-snug mb-2 group-hover:text-violet-700 transition-colors line-clamp-2">
                    <Link href={`/articles/${post.slug}`}>{post.title}</Link>
                </h2>
                {post.excerpt && (
                    <p className="text-slate-500 text-sm line-clamp-2 mb-4">{post.excerpt}</p>
                )}
                <div className="flex items-center justify-between pt-3 border-t border-slate-100">
                    <div className="flex items-center gap-2">
                        <div className="w-7 h-7 rounded-full bg-violet-100 flex items-center justify-center text-violet-700 font-bold text-xs">
                            {post.user?.name?.charAt(0).toUpperCase()}
                        </div>
                        <span className="text-xs text-slate-500">{post.user?.name}</span>
                    </div>
                    <span className="text-xs text-slate-400">
                        {post.published_at ? new Date(post.published_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : ''}
                    </span>
                </div>
            </div>
        </article>
    );
}

export default function PostIndex({ posts, filters, categories }) {
    const [search, setSearch] = useState(filters?.search || '');
    const [category, setCategory] = useState(filters?.category || '');

    function handleFilter(e) {
        e.preventDefault();
        const params = {};
        if (search) params.search = search;
        if (category) params.category = category;
        router.get('/articles', params, { preserveState: true });
    }

    function handleReset() {
        setSearch('');
        setCategory('');
        router.get('/articles');
    }

    return (
        <div className="min-h-screen bg-slate-50">
            <PublicNavbar />

            {!filters?.search && !filters?.category && posts.current_page === 1 && (
                <section className="bg-gradient-to-br from-violet-900 via-violet-800 to-indigo-900 text-white py-20 px-4">
                    <div className="max-w-4xl mx-auto text-center">
                        <div className="inline-flex items-center gap-2 bg-violet-700/50 text-violet-200 text-sm font-medium px-4 py-1.5 rounded-full mb-6">
                            ✨ Platform Publikasi Artikel Modern
                        </div>
                        <h1 className="text-4xl md:text-6xl font-black leading-tight mb-6">
                            Tulis, Bagikan &amp;<br />
                            <span className="text-violet-300">Ilhami Dunia</span>
                        </h1>
                        <p className="text-violet-200 text-lg max-w-2xl mx-auto mb-8">
                            BlogVerse Pro adalah platform tempat para penulis berbagi pengetahuan, pengalaman, dan ide-ide segar dengan komunitas yang aktif.
                        </p>
                        <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
                            <Link href="/register" className="bg-white text-violet-900 hover:bg-violet-50 font-bold px-8 py-3.5 rounded-xl transition-all shadow-lg cursor-pointer">
                                ✨ Mulai Menulis Gratis
                            </Link>
                            <Link href="/login" className="border border-violet-400 text-violet-200 hover:bg-violet-700/50 font-semibold px-8 py-3.5 rounded-xl transition-all cursor-pointer">
                                🔐 Masuk ke Akun
                            </Link>
                        </div>
                    </div>
                </section>
            )}

            <div className="max-w-7xl mx-auto px-4 py-8">
                <form onSubmit={handleFilter} className="flex flex-col md:flex-row gap-3 mb-8">
                    <input
                        type="text"
                        value={search}
                        onChange={e => setSearch(e.target.value)}
                        placeholder="Cari artikel..."
                        className="flex-1 border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 bg-white"
                    />
                    <select
                        value={category}
                        onChange={e => setCategory(e.target.value)}
                        className="border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 bg-white cursor-pointer"
                    >
                        <option value="">Semua Kategori</option>
                        {categories?.map(cat => (
                            <option key={cat.value} value={cat.value}>{cat.label}</option>
                        ))}
                    </select>
                    <button type="submit" className="bg-violet-600 hover:bg-violet-700 text-white px-6 py-2.5 rounded-xl text-sm font-medium transition-all cursor-pointer">
                        🔍 Cari
                    </button>
                    {(filters?.search || filters?.category) && (
                        <button type="button" onClick={handleReset}
                            className="border border-slate-300 hover:bg-slate-100 px-4 py-2.5 rounded-xl text-sm cursor-pointer">
                            ✕ Reset
                        </button>
                    )}
                </form>

                {posts.data?.length > 0 ? (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {posts.data.map(post => <PostCard key={post.id} post={post} />)}
                    </div>
                ) : (
                    <div className="text-center py-20 text-slate-400">
                        <div className="text-6xl mb-4">📭</div>
                        <p className="text-lg font-semibold">Tidak ada artikel ditemukan</p>
                        <p className="text-sm mt-1">Coba ubah filter pencarian Anda</p>
                    </div>
                )}

                {posts.last_page > 1 && (
                    <div className="flex justify-center gap-2 mt-10">
                        {posts.links?.map((link, i) => (
                            <button
                                key={i}
                                onClick={() => link.url && router.get(link.url)}
                                disabled={!link.url}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                                className={`px-3 py-1.5 rounded-lg text-sm transition-all cursor-pointer ${link.active ? 'bg-violet-600 text-white' : link.url ? 'bg-white border border-slate-300 hover:bg-slate-50' : 'opacity-40 cursor-not-allowed'}`}
                            />
                        ))}
                    </div>
                )}
            </div>

            <footer className="bg-slate-900 text-slate-400 mt-16">
                <div className="max-w-7xl mx-auto px-4 py-10 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div className="flex items-center gap-2 font-bold text-white">
                        <div className="w-7 h-7 bg-violet-600 rounded-lg flex items-center justify-center text-xs font-black">B</div>
                        BlogVerse Pro
                    </div>
                    <div className="flex items-center gap-6 text-sm">
                        <Link href="/" className="hover:text-white transition-colors">Beranda</Link>
                        <Link href="/articles" className="hover:text-white transition-colors">Artikel</Link>
                        <Link href="/login" className="hover:text-white transition-colors">Masuk</Link>
                        <Link href="/register" className="hover:text-white transition-colors">Daftar</Link>
                    </div>
                    <p className="text-xs">© 2026 BlogVerse Pro. All rights reserved.</p>
                </div>
            </footer>
        </div>
    );
}
