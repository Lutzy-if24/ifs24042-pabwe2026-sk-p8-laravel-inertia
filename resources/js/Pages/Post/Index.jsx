import { Link, router, useForm, usePage } from '@inertiajs/react';
import { useState, useEffect, useRef } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import '@toast-ui/editor/dist/toastui-editor.css';
import '@toast-ui/editor/dist/toastui-editor-viewer.css';

const CATEGORY_COLORS = {
    'Teknologi': 'bg-blue-100 text-blue-700',
    'Pendidikan': 'bg-emerald-100 text-emerald-700',
    'Gaya Hidup': 'bg-orange-100 text-orange-700',
    'Bisnis': 'bg-amber-100 text-amber-700',
    'Opini': 'bg-purple-100 text-purple-700',
    'Umum': 'bg-slate-100 text-slate-700',
};

// ── TOAST UI Editor Modal ──────────────────────────────────────────────
function PostModal({ mode, post, categories, onClose }) {
    const isAdd = mode === 'add';
    const { data, setData, post: submit, put, processing, errors, reset } = useForm({
        title: post?.title || '',
        category: post?.category || (categories?.[0]?.value || ''),
        excerpt: post?.excerpt || '',
        content: post?.content || '',
        is_published: post?.is_published ?? false,
    });

    const editorRef = useRef(null);
    const editorInstanceRef = useRef(null);

    // ── Cover (hanya mode ubah) ──
    const coverInputRef = useRef(null);
    const [coverFile, setCoverFile] = useState(null);
    const [coverPreview, setCoverPreview] = useState(null);
    const [coverBusy, setCoverBusy] = useState(false);
    const [coverError, setCoverError] = useState('');

    useEffect(() => {
        return () => {
            if (coverPreview) URL.revokeObjectURL(coverPreview);
        };
    }, [coverPreview]);

    function handleCoverChange(e) {
        const file = e.target.files?.[0];
        setCoverError('');
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            setCoverError('Ukuran gambar maksimal 2 MB.');
            e.target.value = '';
            return;
        }
        setCoverFile(file);
        setCoverPreview(URL.createObjectURL(file));
    }

    function uploadCover() {
        if (!coverFile || !post?.id) return;
        setCoverBusy(true);
        setCoverError('');
        router.post(`/dashboard/posts/${post.id}/cover`, { cover: coverFile }, {
            forceFormData: true,
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                setCoverFile(null);
                setCoverPreview(null);
                if (coverInputRef.current) coverInputRef.current.value = '';
            },
            onError: (errs) => setCoverError(errs?.cover || 'Gagal mengunggah cover.'),
            onFinish: () => setCoverBusy(false),
        });
    }

    function removeCover() {
        if (!post?.id) return;
        setCoverBusy(true);
        setCoverError('');
        router.delete(`/dashboard/posts/${post.id}/cover`, {
            preserveState: true,
            preserveScroll: true,
            onError: () => setCoverError('Gagal menghapus cover.'),
            onFinish: () => setCoverBusy(false),
        });
    }

    const currentCover = coverPreview || (post?.cover_image_path ? `/storage/${post.cover_image_path}` : null);

    // ── Editor ──
    useEffect(() => {
        let mounted = true;
        import('@toast-ui/editor').then((mod) => {
            const Editor = [mod.default, mod.default?.default, mod.Editor].find(
                (v) => typeof v === 'function'
            );
            if (!Editor || !mounted || !editorRef.current || editorInstanceRef.current) return;
            editorInstanceRef.current = new Editor({
                el: editorRef.current,
                height: '300px',
                initialEditType: 'markdown',
                previewStyle: 'tab',
                initialValue: data.content,
                events: {
                    change: () => {
                        if (editorInstanceRef.current) {
                            setData('content', editorInstanceRef.current.getMarkdown());
                        }
                    },
                },
            });
        });
        return () => {
            mounted = false;
            if (editorInstanceRef.current) {
                editorInstanceRef.current.destroy();
                editorInstanceRef.current = null;
            }
        };
    }, []);

    function handleSubmit(e) {
        e.preventDefault();
        if (editorInstanceRef.current) {
            setData('content', editorInstanceRef.current.getMarkdown());
        }
        const opts = { onSuccess: () => { reset(); onClose(); } };
        if (isAdd) {
            submit(route('dashboard.posts.store'), opts);
        } else {
            put(route('dashboard.posts.update', post.id), opts);
        }
    }

    return (
        <div className="fixed inset-0 z-50 flex flex-col bg-white">
            {/* Header */}
            <div className={`flex items-center justify-between px-6 py-4 border-b ${isAdd ? 'bg-violet-700 text-white' : 'bg-amber-600 text-white'}`}>
                <h2 className="text-lg font-bold">{isAdd ? '✍️ Tambah Artikel Baru' : '✏️ Ubah Artikel'}</h2>
                <button onClick={onClose} className="text-white/70 hover:text-white text-2xl cursor-pointer leading-none">✕</button>
            </div>

            {/* Body */}
            <form onSubmit={handleSubmit} className="flex flex-col flex-1 overflow-hidden">
                <div className="flex-1 overflow-y-auto p-6 space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-slate-700 mb-1">Judul <span className="text-red-500">*</span></label>
                        <input type="text" value={data.title} onChange={e => setData('title', e.target.value)}
                            className={`w-full border rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 ${errors.title ? 'border-red-400' : 'border-slate-300'}`}
                            placeholder="Judul artikel yang menarik..." />
                        {errors.title && <p className="text-red-500 text-xs mt-1">{errors.title}</p>}
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-slate-700 mb-1">Kategori <span className="text-red-500">*</span></label>
                            <select value={data.category} onChange={e => setData('category', e.target.value)}
                                className="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 cursor-pointer bg-white">
                                {categories?.map(c => <option key={c.value} value={c.value}>{c.label}</option>)}
                            </select>
                            {errors.category && <p className="text-red-500 text-xs mt-1">{errors.category}</p>}
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-slate-700 mb-1">Status</label>
                            <label className="flex items-center gap-2 cursor-pointer mt-2">
                                <input type="checkbox" checked={data.is_published} onChange={e => setData('is_published', e.target.checked)}
                                    className="w-4 h-4 accent-violet-600" />
                                <span className="text-sm text-slate-700">Publikasikan langsung</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-slate-700 mb-1">Ringkasan</label>
                        <textarea value={data.excerpt} onChange={e => setData('excerpt', e.target.value)}
                            rows={2} placeholder="Ringkasan singkat artikel (opsional)..."
                            className="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 resize-none" />
                        {errors.excerpt && <p className="text-red-500 text-xs mt-1">{errors.excerpt}</p>}
                    </div>

                    {/* Foto Cover */}
                    <div>
                        <label className="block text-sm font-medium text-slate-700 mb-1">Foto Cover</label>
                        {isAdd ? (
                            <p className="text-xs text-slate-500 bg-slate-50 border border-dashed border-slate-300 rounded-lg px-4 py-3">
                                💡 Simpan artikel terlebih dahulu, lalu pilih menu <strong>Ubah</strong> untuk mengunggah foto cover.
                            </p>
                        ) : (
                            <div className="border border-slate-300 rounded-lg p-4">
                                {currentCover ? (
                                    <img src={currentCover} alt="Cover artikel" className="w-full max-h-56 object-cover rounded-lg mb-3" />
                                ) : (
                                    <div className="w-full h-32 bg-slate-50 border border-dashed border-slate-300 rounded-lg mb-3 flex items-center justify-center text-sm text-slate-400">
                                        Belum ada cover
                                    </div>
                                )}
                                <div className="flex flex-wrap items-center gap-2">
                                    <input
                                        ref={coverInputRef}
                                        type="file"
                                        accept="image/jpeg,image/png,image/webp"
                                        onChange={handleCoverChange}
                                        className="text-sm text-slate-600 file:mr-3 file:px-4 file:py-2 file:rounded-lg file:border-0 file:bg-violet-50 file:text-violet-700 file:font-medium file:cursor-pointer cursor-pointer"
                                    />
                                    {coverFile && (
                                        <button type="button" onClick={uploadCover} disabled={coverBusy}
                                            className="flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-lg text-sm font-medium cursor-pointer disabled:opacity-60">
                                            {coverBusy ? <span className="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" /> : '📤'}
                                            Unggah Cover
                                        </button>
                                    )}
                                    {post?.cover_image_path && !coverFile && (
                                        <button type="button" onClick={removeCover} disabled={coverBusy}
                                            className="flex items-center gap-2 border border-red-300 text-red-600 hover:bg-red-50 px-4 py-2 rounded-lg text-sm font-medium cursor-pointer disabled:opacity-60">
                                            {coverBusy ? <span className="w-4 h-4 border-2 border-red-300 border-t-red-600 rounded-full animate-spin" /> : '🗑️'}
                                            Hapus Cover
                                        </button>
                                    )}
                                </div>
                                <p className="text-xs text-slate-400 mt-2">Format JPG, PNG, atau WebP. Maksimal 2 MB.</p>
                                {coverError && <p className="text-red-500 text-xs mt-1">{coverError}</p>}
                            </div>
                        )}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-slate-700 mb-1">Konten <span className="text-red-500">*</span></label>
                        <div className="border border-slate-300 rounded-lg overflow-hidden">
                            <div ref={editorRef} />
                        </div>
                        {errors.content && <p className="text-red-500 text-xs mt-1">{errors.content}</p>}
                    </div>
                </div>

                {/* Footer */}
                <div className={`flex items-center justify-end gap-3 px-6 py-4 border-t ${isAdd ? 'bg-violet-50' : 'bg-amber-50'}`}>
                    <button type="button" onClick={onClose}
                        className="border border-slate-300 hover:bg-slate-100 px-5 py-2.5 rounded-lg text-sm font-medium transition-all cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" disabled={processing}
                        className={`flex items-center gap-2 text-white px-6 py-2.5 rounded-lg text-sm font-medium transition-all cursor-pointer disabled:opacity-60 ${isAdd ? 'bg-violet-600 hover:bg-violet-700' : 'bg-amber-600 hover:bg-amber-700'}`}>
                        {processing ? <span className="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" /> : null}
                        {isAdd ? 'Simpan Artikel' : 'Perbarui Artikel'}
                    </button>
                </div>
            </form>
        </div>
    );
}

// ── Post Detail Modal ──────────────────────────────────────────────────
function PostDetailModal({ post, onClose }) {
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

    return (
        <div className="fixed inset-0 z-50 flex flex-col bg-white">
            <div className="flex items-center justify-between px-6 py-4 border-b bg-slate-800 text-white">
                <h2 className="text-lg font-bold">👁️ Detail Artikel</h2>
                <button onClick={onClose} className="text-white/70 hover:text-white text-2xl cursor-pointer">✕</button>
            </div>
            <div className="flex-1 overflow-y-auto p-6">
                <div className="max-w-3xl mx-auto">
                    {post.cover_image_path && (
                        <img src={`/storage/${post.cover_image_path}`} alt={post.title} className="w-full h-64 object-cover rounded-xl mb-6" />
                    )}
                    <span className={`text-xs font-semibold px-3 py-1 rounded-full ${CATEGORY_COLORS[post.category] || 'bg-slate-100 text-slate-700'}`}>{post.category}</span>
                    <h1 className="text-2xl font-black text-slate-900 mt-4 mb-2">{post.title}</h1>
                    {post.excerpt && <p className="text-slate-500 italic mb-6">{post.excerpt}</p>}
                    <div className="border border-slate-200 rounded-xl p-6">
                        <div ref={viewerRef} />
                    </div>
                </div>
            </div>
            <div className="px-6 py-4 border-t bg-slate-50 flex justify-end">
                <button onClick={onClose} className="border border-slate-300 hover:bg-slate-100 px-5 py-2.5 rounded-lg text-sm font-medium cursor-pointer">Tutup</button>
            </div>
        </div>
    );
}

// ── AI Generator Modal ─────────────────────────────────────────────────
function AiGeneratorModal({ categories, onClose }) {
    const [count, setCount] = useState(3);
    const [loading, setLoading] = useState(false);
    const [generated, setGenerated] = useState([]);
    const [selected, setSelected] = useState([]);
    const [error, setError] = useState('');
    const [saving, setSaving] = useState(false);

    async function handleGenerate(e) {
        e.preventDefault();
        if (count < 1 || count > 10) { setError('Jumlah artikel harus antara 1-10.'); return; }
        setLoading(true); setError(''); setGenerated([]); setSelected([]);
        try {
            const res = await fetch(route('dashboard.posts.ai-generate'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                body: JSON.stringify({ count }),
            });
            const json = await res.json();
            if (json.success) { setGenerated(json.posts); setSelected(json.posts.map((_, i) => i)); }
            else { setError(json.message || 'Gagal generate artikel.'); }
        } catch { setError('Terjadi kesalahan jaringan.'); }
        finally { setLoading(false); }
    }

    function toggleSelect(i) { setSelected(s => s.includes(i) ? s.filter(x => x !== i) : [...s, i]); }

    function handleSave() {
        const toSave = selected.map(i => generated[i]);
        if (!toSave.length) { setError('Pilih minimal 1 artikel.'); return; }
        setSaving(true);
        router.post(route('dashboard.posts.ai-save'), { posts: toSave }, {
            onSuccess: () => onClose(),
            onError: () => { setSaving(false); setError('Gagal menyimpan.'); },
            onFinish: () => setSaving(false),
        });
    }

    return (
        <div className="fixed inset-0 z-50 flex flex-col bg-white">
            <div className="flex items-center justify-between px-6 py-4 border-b bg-gradient-to-r from-indigo-700 to-violet-700 text-white">
                <h2 className="text-lg font-bold">🤖 AI Generator Artikel</h2>
                <button onClick={onClose} className="text-white/70 hover:text-white text-2xl cursor-pointer">✕</button>
            </div>
            <div className="flex-1 overflow-y-auto p-6">
                <form onSubmit={handleGenerate} className="flex items-end gap-3 mb-6">
                    <div>
                        <label className="block text-sm font-medium text-slate-700 mb-1">Jumlah Artikel (1–10)</label>
                        <input
                            type="number"
                            min={1}
                            max={10}
                            value={count}
                            onChange={e => { const v = parseInt(e.target.value); setCount(isNaN(v) ? '' : v); }}
                            className="w-32 border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500"
                        />
                    </div>
                    <button type="submit" disabled={loading}
                        className="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium cursor-pointer disabled:opacity-60 transition-all">
                        {loading ? <span className="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" /> : '✨'}
                        {loading ? 'Memproses...' : 'Generate'}
                    </button>
                </form>

                {error && <div className="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm mb-4">{error}</div>}

                {generated.length > 0 && (
                    <div>
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="font-semibold text-slate-800">Hasil Generate ({generated.length} artikel) – Pilih yang ingin disimpan:</h3>
                            <div className="flex gap-2">
                                <button onClick={() => setSelected(generated.map((_, i) => i))} className="text-xs text-violet-600 hover:underline cursor-pointer">Pilih Semua</button>
                                <span className="text-slate-300">|</span>
                                <button onClick={() => setSelected([])} className="text-xs text-slate-500 hover:underline cursor-pointer">Hapus Semua</button>
                            </div>
                        </div>
                        <div className="space-y-3">
                            {generated.map((post, i) => (
                                <div key={i} onClick={() => toggleSelect(i)}
                                    className={`border rounded-xl p-4 cursor-pointer transition-all ${selected.includes(i) ? 'border-violet-400 bg-violet-50' : 'border-slate-200 hover:border-slate-300'}`}>
                                    <div className="flex items-start gap-3">
                                        <input type="checkbox" checked={selected.includes(i)} readOnly className="mt-1 accent-violet-600" />
                                        <div className="flex-1 min-w-0">
                                            <div className="flex items-center gap-2 mb-1">
                                                <span className={`text-xs font-semibold px-2 py-0.5 rounded-full ${CATEGORY_COLORS[post.category] || 'bg-slate-100 text-slate-700'}`}>{post.category}</span>
                                            </div>
                                            <h4 className="font-semibold text-slate-900 text-sm mb-1">{post.title}</h4>
                                            <p className="text-slate-500 text-xs line-clamp-2">{post.excerpt}</p>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </div>
            <div className="px-6 py-4 border-t bg-indigo-50 flex items-center justify-end gap-3">
                <button onClick={onClose} className="border border-slate-300 hover:bg-slate-100 px-5 py-2.5 rounded-lg text-sm font-medium cursor-pointer">Batal</button>
                {generated.length > 0 && (
                    <button onClick={handleSave} disabled={saving || !selected.length}
                        className="flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium cursor-pointer disabled:opacity-60">
                        {saving ? <span className="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" /> : null}
                        Simpan {selected.length} Artikel
                    </button>
                )}
            </div>
        </div>
    );
}

// ── Kebab Menu ─────────────────────────────────────────────────────────
function KebabMenu({ post, onView, onEdit, onDelete }) {
    const [open, setOpen] = useState(false);
    return (
        <div className="relative">
            <button onClick={() => setOpen(!open)} className="p-1.5 rounded-lg hover:bg-slate-100 cursor-pointer text-slate-500 hover:text-slate-800 transition-colors" title="Aksi">⋮</button>
            {open && (
                <>
                    <div className="fixed inset-0 z-10" onClick={() => setOpen(false)} />
                    <div className="absolute right-0 mt-1 w-40 bg-white rounded-xl shadow-xl border border-slate-100 py-1 z-20">
                        <button onClick={() => { setOpen(false); onView(); }} className="flex items-center gap-2 w-full px-3 py-2 text-sm text-violet-700 hover:bg-violet-50 cursor-pointer">
                            👁️ Detail
                        </button>
                        <button onClick={() => { setOpen(false); onEdit(); }} className="flex items-center gap-2 w-full px-3 py-2 text-sm text-violet-600 hover:bg-violet-50 cursor-pointer">
                            ✏️ Ubah
                        </button>
                        <button onClick={() => { setOpen(false); onDelete(); }} className="flex items-center gap-2 w-full px-3 py-2 text-sm text-red-600 hover:bg-red-50 cursor-pointer">
                            🗑️ Hapus
                        </button>
                    </div>
                </>
            )}
        </div>
    );
}

// ── Main Page ──────────────────────────────────────────────────────────
export default function PostIndex({ posts, filters, stats, categories }) {
    const [modal, setModal] = useState(null); // null | {type: 'add'|'edit'|'detail'|'ai', post?}
    const [confirmDelete, setConfirmDelete] = useState(null);
    const [publishLoading, setPublishLoading] = useState(null);
    const [importLoading, setImportLoading] = useState(false);
    const [search, setSearch] = useState(filters?.search || '');
    const fileRef = useRef(null);

    // Ambil data artikel terbaru dari props, supaya cover yang baru diunggah langsung terlihat di modal
    const activePost = modal?.post
        ? (posts.data?.find(p => p.id === modal.post.id) ?? modal.post)
        : null;

    function handleSearch(e) {
        e.preventDefault();
        router.get(route('dashboard.posts.index'), { search }, { preserveState: true });
    }

    function handleDelete(post) {
        router.delete(route('dashboard.posts.destroy', post.id), {
            onSuccess: () => setConfirmDelete(null),
        });
    }

    function handlePublish(post) {
        setPublishLoading(post.id);
        router.patch(route('dashboard.posts.publish', post.id), {}, {
            onFinish: () => setPublishLoading(null),
        });
    }

    function handleImport(e) {
        const file = e.target.files?.[0];
        if (!file) return;
        setImportLoading(true);
        const form = new FormData();
        form.append('file', file);
        router.post(route('dashboard.posts.import'), form, {
            onFinish: () => { setImportLoading(false); if (fileRef.current) fileRef.current.value = ''; },
        });
    }

    return (
        <AppLayout title="Artikel Saya">
            {/* Modals */}
            {modal?.type === 'add' && <PostModal mode="add" categories={categories} onClose={() => setModal(null)} />}
            {modal?.type === 'edit' && <PostModal mode="edit" post={activePost} categories={categories} onClose={() => setModal(null)} />}
            {modal?.type === 'detail' && <PostDetailModal post={activePost} onClose={() => setModal(null)} />}
            {modal?.type === 'ai' && <AiGeneratorModal categories={categories} onClose={() => setModal(null)} />}

            {/* Confirm Delete */}
            {confirmDelete && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                    <div className="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl">
                        <div className="text-4xl mb-3 text-center">🗑️</div>
                        <h3 className="text-lg font-bold text-center mb-2">Hapus Artikel?</h3>
                        <p className="text-sm text-slate-500 text-center mb-6">Artikel "<strong>{confirmDelete.title}</strong>" akan dihapus permanen beserta cover-nya.</p>
                        <div className="flex gap-3">
                            <button onClick={() => setConfirmDelete(null)} className="flex-1 border border-slate-300 hover:bg-slate-100 py-2.5 rounded-lg text-sm font-medium cursor-pointer">Batal</button>
                            <button onClick={() => handleDelete(confirmDelete)} className="flex-1 bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-lg text-sm font-medium cursor-pointer">Hapus</button>
                        </div>
                    </div>
                </div>
            )}

            {/* Stats Cards */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                {[
                    { label: 'Total Artikel', value: stats?.total ?? 0, icon: '📝', color: 'from-violet-500 to-violet-600' },
                    { label: 'Total Selesai', value: stats?.published ?? 0, icon: '✅', color: 'from-emerald-500 to-emerald-600' },
                    { label: 'Belum Selesai', value: stats?.draft ?? 0, icon: '📋', color: 'from-amber-500 to-amber-600' },
                ].map(stat => (
                    <div key={stat.label} className={`bg-gradient-to-br ${stat.color} text-white rounded-2xl p-5 shadow-sm`}>
                        <div className="text-3xl mb-2">{stat.icon}</div>
                        <div className="text-3xl font-black">{stat.value}</div>
                        <div className="text-white/80 text-sm font-medium mt-1">{stat.label}</div>
                    </div>
                ))}
            </div>

            {/* Toolbar */}
            <div className="bg-white rounded-xl border border-slate-200 p-4 mb-4">
                <div className="flex flex-col md:flex-row gap-3">
                    <form onSubmit={handleSearch} className="flex gap-2 flex-1">
                        <input type="text" value={search} onChange={e => setSearch(e.target.value)} placeholder="Cari artikel..."
                            className="flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500" />
                        <button type="submit" className="bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-lg text-sm cursor-pointer">🔍</button>
                    </form>
                    <div className="flex flex-wrap gap-2">
                        <button onClick={() => setModal({ type: 'add' })}
                            className="bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-lg text-sm font-medium cursor-pointer flex items-center gap-1.5 transition-all">
                            ➕ Tambah
                        </button>
                        <button onClick={() => setModal({ type: 'ai' })}
                            className="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium cursor-pointer flex items-center gap-1.5">
                            🤖 AI Generate
                        </button>
                        <a href={route('dashboard.posts.export')}
                            className="border border-emerald-500 text-emerald-700 hover:bg-emerald-50 px-4 py-2 rounded-lg text-sm font-medium cursor-pointer flex items-center gap-1.5">
                            📥 Ekspor Excel
                        </a>
                        <button onClick={() => fileRef.current?.click()} disabled={importLoading}
                            className="border border-slate-300 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-medium cursor-pointer flex items-center gap-1.5 disabled:opacity-60">
                            {importLoading ? <span className="w-3 h-3 border-2 border-slate-400 border-t-slate-800 rounded-full animate-spin" /> : '📤'}
                            Impor Excel
                        </button>
                        <input ref={fileRef} type="file" accept=".xlsx,.xls" className="hidden" onChange={handleImport} />
                    </div>
                </div>
            </div>

            {/* Post List */}
            <div className="bg-white rounded-xl border border-slate-200 overflow-hidden">
                {posts.data?.length > 0 ? (
                    <div>
                        <div className="hidden md:grid grid-cols-12 gap-4 px-5 py-3 bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                            <div className="col-span-5">Judul</div>
                            <div className="col-span-2">Kategori</div>
                            <div className="col-span-2">Status</div>
                            <div className="col-span-2">Tanggal</div>
                            <div className="col-span-1 text-right">Aksi</div>
                        </div>
                        <div className="divide-y divide-slate-100">
                            {posts.data.map(post => (
                                <div key={post.id} className="grid grid-cols-12 gap-4 px-5 py-4 items-center hover:bg-slate-50 transition-colors">
                                    <div className="col-span-10 md:col-span-5">
                                        <p className="font-semibold text-slate-900 text-sm truncate">{post.title}</p>
                                        <p className="text-xs text-slate-400 mt-0.5 truncate">{post.slug}</p>
                                    </div>
                                    <div className="hidden md:block col-span-2">
                                        <span className={`text-xs font-semibold px-2.5 py-1 rounded-full ${CATEGORY_COLORS[post.category] || 'bg-slate-100 text-slate-700'}`}>
                                            {post.category}
                                        </span>
                                    </div>
                                    <div className="hidden md:flex col-span-2 items-center gap-2">
                                        <button onClick={() => handlePublish(post)} disabled={publishLoading === post.id}
                                            className={`text-xs font-semibold px-2.5 py-1 rounded-full cursor-pointer transition-all ${post.is_published ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'}`}>
                                            {publishLoading === post.id ? '...' : post.is_published ? '✅ Published' : '📋 Draf'}
                                        </button>
                                    </div>
                                    <div className="hidden md:block col-span-2 text-xs text-slate-400">
                                        {post.created_at ? new Date(post.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '—'}
                                    </div>
                                    <div className="col-span-2 md:col-span-1 flex justify-end">
                                        <KebabMenu
                                            post={post}
                                            onView={() => setModal({ type: 'detail', post })}
                                            onEdit={() => setModal({ type: 'edit', post })}
                                            onDelete={() => setConfirmDelete(post)}
                                        />
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                ) : (
                    <div className="text-center py-16 text-slate-400">
                        <div className="text-5xl mb-3">📭</div>
                        <p className="font-semibold">Belum ada artikel</p>
                        <p className="text-sm mt-1">Klik "Tambah" untuk membuat artikel pertama Anda</p>
                    </div>
                )}

                {/* Pagination */}
                {posts.last_page > 1 && (
                    <div className="flex justify-center gap-1 p-4 border-t border-slate-100">
                        {posts.links?.map((link, i) => (
                            <button key={i} onClick={() => link.url && router.get(link.url)}
                                disabled={!link.url}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                                className={`px-3 py-1.5 rounded-lg text-xs transition-all cursor-pointer ${link.active ? 'bg-violet-600 text-white' : link.url ? 'border border-slate-300 hover:bg-slate-50' : 'opacity-40 cursor-not-allowed'}`}
                            />
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}