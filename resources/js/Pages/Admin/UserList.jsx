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

export default function UserList({ users, filters }) {
    function handleSearch(e) {
        e.preventDefault();
        const q = e.target.search.value;
        router.get(route('admin.users.index'), { search: q }, { preserveState: true });
    }

    function handleDelete(user) {
        if (!confirm(`Hapus pengguna "${user.name}"?`)) return;
        router.delete(route('admin.users.destroy', user.id));
    }

    function handleRole(user, role) {
        if (!confirm(`Ubah role "${user.name}" menjadi ${role}?`)) return;
        router.patch(route('admin.users.role', user.id), { role });
    }

    return (
        <AppLayout title="Kelola Pengguna">
            <div className="mb-6 flex flex-col md:flex-row gap-3 items-start md:items-center justify-between">
                <form onSubmit={handleSearch} className="flex gap-2">
                    <input name="search" aria-label="Cari nama, username, atau email" defaultValue={filters?.search} placeholder="Cari nama, username, email..."
                        className="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 w-64" />
                    <button type="submit" className="bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-lg text-sm cursor-pointer">🔍 Cari</button>
                </form>
            </div>

            <div className="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div className="hidden md:grid grid-cols-12 px-5 py-3 bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase">
                    <div className="col-span-4">Pengguna</div>
                    <div className="col-span-3">Email</div>
                    <div className="col-span-2">Role</div>
                    <div className="col-span-2">Bergabung</div>
                    <div className="col-span-1 text-right">Aksi</div>
                </div>
                <div className="divide-y divide-slate-100">
                    {users.data?.length > 0 ? users.data.map(user => (
                        <div key={user.id} className="grid grid-cols-12 gap-3 px-5 py-4 items-center hover:bg-slate-50">
                            <div className="col-span-10 md:col-span-4 flex items-center gap-3">
                                <div className="w-9 h-9 rounded-full bg-violet-100 flex items-center justify-center font-bold text-violet-700 text-sm flex-shrink-0">
                                    {user.name?.charAt(0).toUpperCase()}
                                </div>
                                <div className="min-w-0">
                                    <p className="font-semibold text-slate-900 text-sm">{user.name}</p>
                                    <p className="text-xs text-slate-500">@{user.username}</p>
                                </div>
                            </div>
                            <div className="hidden md:block col-span-3 text-sm text-slate-500 truncate">{user.email}</div>
                            <div className="hidden md:flex col-span-2 items-center gap-2">
                                <select aria-label={`Ubah role ${user.username}`} value={user.role} onChange={e => handleRole(user, e.target.value)}
                                    className="text-xs border border-slate-300 rounded-lg px-2 py-1 cursor-pointer bg-white focus:outline-none focus:ring-1 focus:ring-violet-500">
                                    <option value="member">Member</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div className="hidden md:block col-span-2 text-xs text-slate-500">
                                {user.created_at ? new Date(user.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '—'}
                            </div>
                            <div className="col-span-2 md:col-span-1 flex justify-end gap-1">
                                <Link href={route('admin.users.posts', user.id)} title="Lihat Artikel" aria-label={`Lihat artikel ${user.name}`}
                                    className="p-1.5 text-violet-600 hover:bg-violet-50 rounded-lg cursor-pointer text-sm">📋</Link>
                                <button onClick={() => handleDelete(user)} title="Hapus" aria-label={`Hapus ${user.name}`}
                                    className="p-1.5 text-red-600 hover:bg-red-50 rounded-lg cursor-pointer text-sm">🗑️</button>
                            </div>
                        </div>
                    )) : (
                        <div className="text-center py-16 text-slate-500">
                            <div className="text-5xl mb-3">👥</div>
                            <p className="font-semibold">Tidak ada pengguna ditemukan</p>
                        </div>
                    )}
                </div>
                {users.last_page > 1 && (
                    <div className="flex justify-center gap-1 p-4 border-t">
                        {users.links?.map((link, i) => (
                            <button key={i} onClick={() => link.url && router.get(link.url)}
                                disabled={!link.url}
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