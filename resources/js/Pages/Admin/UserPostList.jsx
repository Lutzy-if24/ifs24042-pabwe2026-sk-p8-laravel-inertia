import { Link, router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

const CATEGORY_COLORS = {
    'Teknologi': 'bg-blue-100 text-blue-700',
    'Pendidikan': 'bg-emerald-100 text-emerald-700',
    'Gaya Hidup': 'bg-orange-100 text-orange-700',
    'Bisnis': 'bg-amber-100 text-amber-700',
    'Opini': 'bg-purple-100 text-purple-700',
    'Umum': 'bg-slate-100 text-slate-700',
};

export default function UserPostList({ user, posts, filters }) {
    function handleSearch(e) {
        e.preventDefault();
        router.get(route('admin.users.posts', user.id), { search: e.target.search.value }, { preserveState: true });
    }

    return (
        <AppLayout title={`Artikel: ${user.name}`}>
            <div className="mb-4 flex items-center justify-between">
                <div className="flex items-center gap-3">
                    <Link href={route('admin.users.index')} className="text-sm text-violet-600 hover:text-violet-800">← Kembali</Link>
                    <div className="flex items-center gap-2">
                        <div className="w-9 h-9 rounded-full bg-violet-100 flex items-center justify-center font-bold text-violet-700">
                            {user.name?.charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <p className="font-semibold text-slate-900 text-sm">{user.name}</p>
                            <p className="text-xs text-slate-500">@{user.username}</p>
                        </div>
                    </div>
                </div>
                <a href={route('admin.users.posts.export', user.id)}
                    className="border border-emerald-500 text-emerald-700 hover:bg-emerald-50 px-4 py-2 rounded-lg text-sm font-medium cursor-pointer flex items-center gap-1.5">
                    📥 Ekspor Excel
                </a>
            </div>

            <form onSubmit={handleSearch} className="flex gap-2 mb-4">
                <input name="search" aria-label="Cari judul artikel" defaultValue={filters?.search} placeholder="Cari judul artikel..."
                    className="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 w-56" />
                <button type="submit" aria-label="Cari" className="bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-lg text-sm cursor-pointer">🔍</button>
            </form>

            <div className="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div className="hidden md:grid grid-cols-12 px-5 py-3 bg-slate-50 border-b text-xs font-semibold text-slate-500 uppercase">
                    <div className="col-span-5">Judul</div>
                    <div className="col-span-2">Kategori</div>
                    <div className="col-span-2">Status</div>
                    <div className="col-span-3">Tanggal</div>
                </div>
                <div className="divide-y divide-slate-100">
                    {posts.data?.length > 0 ? posts.data.map(post => (
                        <div key={post.id} className="grid grid-cols-12 gap-3 px-5 py-4 items-center hover:bg-slate-50">
                            <div className="col-span-12 md:col-span-5">
                                <p className="font-semibold text-slate-900 text-sm truncate">{post.title}</p>
                            </div>
                            <div className="hidden md:block col-span-2">
                                <span className={`text-xs font-semibold px-2.5 py-1 rounded-full ${CATEGORY_COLORS[post.category] || 'bg-slate-100 text-slate-700'}`}>{post.category}</span>
                            </div>
                            <div className="hidden md:block col-span-2">
                                <span className={`text-xs font-semibold px-2.5 py-1 rounded-full ${post.is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'}`}>
                                    {post.is_published ? '✅ Published' : '📋 Draf'}
                                </span>
                            </div>
                            <div className="hidden md:block col-span-3 text-xs text-slate-500">
                                {post.created_at ? new Date(post.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '—'}
                            </div>
                        </div>
                    )) : (
                        <div className="text-center py-16 text-slate-500">
                            <div className="text-5xl mb-3">📭</div>
                            <p className="font-semibold">Pengguna ini belum punya artikel</p>
                        </div>
                    )}
                </div>
                {posts.last_page > 1 && (
                    <div className="flex justify-center gap-1 p-4 border-t">
                        {posts.links?.map((link, i) => (
                            <button key={i} onClick={() => link.url && router.get(link.url)} disabled={!link.url}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                                className={`px-3 py-1.5 rounded-lg text-xs cursor-pointer ${link.active ? 'bg-violet-600 text-white' : link.url ? 'border border-slate-300 hover:bg-slate-50' : 'opacity-40 cursor-not-allowed'}`}
                            />
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}