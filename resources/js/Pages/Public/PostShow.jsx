import { Link, usePage } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import '@toast-ui/editor/dist/toastui-editor-viewer.css';

function PublicNavbar() {
    const { auth } = usePage().props;
    const user = auth?.user;
    return (
        <nav className="sticky top-0 z-50 bg-white/80 backdrop-blur-xl border-b border-slate-200 shadow-sm">
            <div className="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
                <Link href={route('public.index')} className="flex items-center gap-2.5 font-black text-violet-700 text-xl">
                    <div className="w-8 h-8 bg-violet-700 rounded-lg flex items-center justify-center">
                        <span className="text-white font-black text-sm">B</span>
                    </div>
                    BlogVerse Pro
                </Link>
                <div className="flex items-center gap-3">
                    {user ? (
                        <Link
                            href={user.role === 'admin' ? route('admin.users.index') : route('dashboard.posts.index')}
                            className="bg-violet-600 hover:bg-violet-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-all cursor-pointer"
                        >
                            {user.role === 'admin' ? '🛡️ Panel Admin' : '✍️ Dashboard'}
                        </Link>
                    ) : (
                        <div className="flex items-center gap-2">
                            <Link href={route('login')} className="text-slate-700 hover:text-violet-700 font-medium text-sm px-3 py-2 rounded-lg hover:bg-violet-50 cursor-pointer">
                                🔐 Masuk
                            </Link>
                            <Link href={route('register')} className="bg-violet-600 hover:bg-violet-700 text-white font-medium text-sm px-4 py-2 rounded-lg cursor-pointer">
                                ✨ Daftar
                            </Link>
                        </div>
                    )}
                </div>
            </div>
        </nav>
    );
}

export default function PostShow({ post }) {
    const viewerRef = useRef(null);
    const viewerInstanceRef = useRef(null);

    useEffect(() => {
        let mounted = true;

        import('@toast-ui/editor/dist/toastui-editor-viewer').then((mod) => {
            const Viewer = [mod.default, mod.default?.default, mod.Viewer].find(
                (v) => typeof v === 'function'
            );
            if (!Viewer || !mounted || !viewerRef.current || viewerInstanceRef.current) return;

            viewerInstanceRef.current = new Viewer({
                el: viewerRef.current,
                initialValue: post.content || '',
            });
        });

        return () => {
            mounted = false;
            if (viewerInstanceRef.current) {
                viewerInstanceRef.current.destroy();
                viewerInstanceRef.current = null;
            }
        };
    }, [post.content]);

    const categoryColors = {
        'Teknologi': 'bg-blue-100 text-blue-700',
        'Pendidikan': 'bg-emerald-100 text-emerald-700',
        'Gaya Hidup': 'bg-orange-100 text-orange-700',
        'Bisnis': 'bg-amber-100 text-amber-700',
        'Opini': 'bg-purple-100 text-purple-700',
        'Umum': 'bg-slate-100 text-slate-700',
    };

    return (
        <div className="min-h-screen bg-slate-50">
            <PublicNavbar />

            <article className="max-w-4xl mx-auto px-4 py-10">
                {/* Breadcrumb */}
                <nav className="text-sm text-slate-500 mb-6 flex items-center gap-2">
                    <Link href={route('public.index')} className="hover:text-violet-700">Beranda</Link>
                    <span>/</span>
                    <Link href={route('public.articles')} className="hover:text-violet-700">Artikel</Link>
                    <span>/</span>
                    <span className="text-slate-800 truncate max-w-xs">{post.title}</span>
                </nav>

                {/* Category & Meta */}
                <div className="flex items-center gap-3 mb-4">
                    <span className={`text-xs font-semibold px-3 py-1 rounded-full ${categoryColors[post.category] || 'bg-slate-100 text-slate-700'}`}>
                        {post.category}
                    </span>
                    <span className="text-slate-400 text-sm">·</span>
                    <span className="text-sm text-slate-500">
                        {post.published_at
                            ? new Date(post.published_at).toLocaleDateString('id-ID', {
                                  weekday: 'long',
                                  day: 'numeric',
                                  month: 'long',
                                  year: 'numeric',
                              })
                            : ''}
                    </span>
                </div>

                {/* Title */}
                <h1 className="text-3xl md:text-4xl font-black text-slate-900 leading-tight mb-4">{post.title}</h1>

                {/* Author */}
                <div className="flex items-center gap-3 mb-8 pb-6 border-b border-slate-200">
                    <div className="w-10 h-10 rounded-full bg-violet-100 flex items-center justify-center font-bold text-violet-700">
                        {post.user?.name?.charAt(0).toUpperCase()}
                    </div>
                    <div>
                        <p className="font-semibold text-slate-800 text-sm">{post.user?.name}</p>
                        <p className="text-xs text-slate-500">@{post.user?.username}</p>
                    </div>
                </div>

                {/* Cover Image */}
                {post.cover_image_path && (
                    <div className="rounded-2xl overflow-hidden mb-8">
                        <img src={`/storage/${post.cover_image_path}`} alt={post.title} className="w-full object-cover max-h-96" />
                    </div>
                )}

                {/* Excerpt */}
                {post.excerpt && (
                    <div className="bg-violet-50 border-l-4 border-violet-500 px-6 py-4 rounded-r-xl mb-8">
                        <p className="text-violet-800 font-medium italic">{post.excerpt}</p>
                    </div>
                )}

                {/* Content via TOAST UI Viewer */}
                <div className="prose-content bg-white rounded-2xl p-8 shadow-sm border border-slate-100">
                    <div ref={viewerRef} />
                </div>

                {/* CTA komunitas */}
                <div className="mt-12 bg-gradient-to-br from-violet-900 to-indigo-900 rounded-2xl p-8 text-white text-center">
                    <h2 className="text-2xl font-black mb-2">Bergabung dengan Komunitas Penulis</h2>
                    <p className="text-violet-200 mb-6 max-w-md mx-auto">
                        Bagikan cerita, ilmu, dan pengalaman Anda dengan ribuan pembaca di BlogVerse Pro.
                    </p>
                    <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <Link href={route('register')} className="bg-white text-violet-900 hover:bg-violet-50 font-bold px-7 py-3 rounded-xl transition-all cursor-pointer">
                            ✨ Daftar Gratis
                        </Link>
                        <Link href={route('login')} className="border border-violet-400 text-violet-200 hover:bg-violet-700/50 font-semibold px-7 py-3 rounded-xl transition-all cursor-pointer">
                            🔐 Masuk
                        </Link>
                    </div>
                </div>
            </article>

            {/* Footer */}
            <footer className="bg-slate-900 text-slate-400 mt-16">
                <div className="max-w-7xl mx-auto px-4 py-10 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div className="flex items-center gap-2 font-bold text-white">
                        <div className="w-7 h-7 bg-violet-600 rounded-lg flex items-center justify-center text-xs font-black">B</div>
                        BlogVerse Pro
                    </div>
                    <div className="flex items-center gap-6 text-sm">
                        <Link href={route('public.index')} className="hover:text-white transition-colors">Beranda</Link>
                        <Link href={route('public.articles')} className="hover:text-white transition-colors">Artikel</Link>
                        <Link href={route('login')} className="hover:text-white transition-colors">Masuk</Link>
                        <Link href={route('register')} className="hover:text-white transition-colors">Daftar</Link>
                    </div>
                    <p className="text-xs">© 2026 BlogVerse Pro. All rights reserved.</p>
                </div>
            </footer>
        </div>
    );
}