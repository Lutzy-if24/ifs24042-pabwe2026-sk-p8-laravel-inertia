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

export default function PostList({ posts, filters, categories }) {
    function handleSearch(e) {
        e.preventDefault();
        const q = e.target.search.value;
        const cat = e.target.category.value;
        router.get(route('admin.posts.index'), { search: q, category: cat }, { preserveState: true });
    }

    return (
        <AppLayout title="Semua Artikel">
            <div className="mb-6 flex flex-col md:flex-row gap-3 justify-between items-start md:items-center">
                <form onSubmit={handleSearch} className="flex flex-wrap gap-2">
                    <input name="search" defaultValue={filters?.search} placeholder="Cari judul atau penulis..."
                        className="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 w-56" />
                    <select name="category" defaultValue={filters?.category}
                        className="border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-violet-500 cursor-pointer">
                        <option value="">Semua Kategori</option>
                        {categories?.map(c => <option key={c.value} value={c.value}>{c.label}</option>)}
                    </select>
                    <button type="submit" className="bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-lg text-sm cursor-pointer">🔍</button>
                </form>
               <a href={route('admin.posts.export')}
                    className="border border-emerald-500 text-emerald-700 hover:bg-emerald-50 px-4 py-2 rounded-lg text-sm font-medium cursor-pointer flex items-center gap-1.5">
                    📥 Ekspor Semua
                </a>
            </div>

            <div className="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div className="hidden md:grid grid-cols-12 px-5 py-3 bg-slate-50 border-b text-xs font-semibold text-slate-500 uppercase">
                    <div className="col-span-4">Judul</div>
                    <div className="col-span-2">Kategori</div>
                    <div className="col-span-2">Penulis</div>
                    <div className="col-span-2">Status</div>
                    <div className="col-span-2">Tanggal</div>
                </div>
                <div className="divide-y divide-slate-100">
                    {posts.data?.length > 0 ? posts.data.map(post => (
                        <div key={post.id} className="grid grid-cols-12 gap-3 px-5 py-4 items-center hover:bg-slate-50">
                            <div className="col-span-12 md:col-span-4">
                                <p className="font-semibold text-slate-900 text-sm truncate">{post.title}</p>
                                <p className="text-xs text-slate-400 truncate">{post.slug}</p>
                            </div>
                            <div className="hidden md:block col-span-2">
                                <span className={`text-xs font-semibold px-2.5 py-1 rounded-full ${CATEGORY_COLORS[post.category] || 'bg-slate-100 text-slate-700'}`}>
                                    {post.category}
                                </span>
                            </div>
                            <div className="hidden md:block col-span-2 text-sm text-slate-600">
                                {post.user?.name}
                                <p className="text-xs text-slate-400">@{post.user?.username}</p>
                            </div>
                            <div className="hidden md:block col-span-2">
                                <span className={`text-xs font-semibold px-2.5 py-1 rounded-full ${post.is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'}`}>
                                    {post.is_published ? '✅ Published' : '📋 Draf'}
                                </span>
                            </div>
                            <div className="hidden md:block col-span-2 text-xs text-slate-400">
                                {post.created_at ? new Date(post.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '—'}
                            </div>
                        </div>
                    )) : (
                        <div className="text-center py-16 text-slate-400">
                            <div className="text-5xl mb-3">📭</div>
                            <p className="font-semibold">Tidak ada artikel ditemukan</p>
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
