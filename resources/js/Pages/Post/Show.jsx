import { Link } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import AppLayout from '../../Layouts/AppLayout';

export default function PostShow({ post }) {
    const viewerRef = useRef(null);
    const viewerInstanceRef = useRef(null);

    useEffect(() => {
        let mounted = true;
        import('@toast-ui/editor').then(({ Viewer }) => {
            if (mounted && viewerRef.current && !viewerInstanceRef.current) {
                viewerInstanceRef.current = new Viewer({ el: viewerRef.current, initialValue: post.content || '' });
            }
        });
        return () => { mounted = false; if (viewerInstanceRef.current) { viewerInstanceRef.current.destroy(); viewerInstanceRef.current = null; } };
    }, [post.content]);

    return (
        <AppLayout title="Detail Artikel">
            <div className="max-w-3xl">
                <div className="mb-4">
                    <Link href={route('dashboard.posts.index')} className="text-sm text-violet-600 hover:text-violet-800 flex items-center gap-1">
                        ← Kembali ke Daftar
                    </Link>
                </div>
                <div className="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                    {post.cover_image_path && (
                        <img src={`/storage/${post.cover_image_path}`} alt={post.title} className="w-full h-64 object-cover" />
                    )}
                    <div className="p-6">
                        <span className={`text-xs font-semibold px-3 py-1 rounded-full bg-violet-100 text-violet-700`}>{post.category}</span>
                        <h1 className="text-2xl font-black text-slate-900 mt-4 mb-2">{post.title}</h1>
                        {post.excerpt && <p className="text-slate-500 italic mb-4">{post.excerpt}</p>}
                        <div className="border border-slate-200 rounded-xl p-4">
                            <link rel="stylesheet" href="https://uicdn.toast.com/editor/latest/toastui-editor-viewer.min.css" />
                            <div ref={viewerRef} />
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
