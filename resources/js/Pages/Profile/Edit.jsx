import { useForm, router } from '@inertiajs/react';
import { useRef } from 'react';
import AppLayout from '../../Layouts/AppLayout';

export default function ProfileEdit({ user }) {
    const { data, setData, put, processing, errors } = useForm({
        name: user.name || '',
        username: user.username || '',
        email: user.email || '',
        password: '',
        password_confirmation: '',
    });

    const photoRef = useRef(null);

    function handleSubmit(e) {
        e.preventDefault();
        put(route('profile.update'));
    }

    function handlePhotoChange(e) {
        const file = e.target.files?.[0];
        if (!file) return;
        const form = new FormData();
        form.append('photo', file);
        router.post(route('profile.photo'), form);
    }

    return (
        <AppLayout title="Edit Profil">
            <div className="max-w-2xl">
                {/* Avatar */}
                <div className="bg-white rounded-2xl border border-slate-200 p-6 mb-6">
                    <h2 className="text-lg font-bold text-slate-800 mb-4">Foto Profil</h2>
                    <div className="flex items-center gap-6">
                        {user.profile_photo_path ? (
                            <img src={`/storage/${user.profile_photo_path}`} alt={user.name}
                                className="w-20 h-20 rounded-full object-cover border-4 border-violet-200" />
                        ) : (
                            <div className="w-20 h-20 rounded-full bg-violet-100 flex items-center justify-center font-black text-violet-700 text-3xl border-4 border-violet-200">
                                {user.name?.charAt(0).toUpperCase()}
                            </div>
                        )}
                        <div>
                            <p className="text-sm text-slate-500 mb-2">JPG, PNG, atau WebP. Maks 1MB.</p>
                            <button onClick={() => photoRef.current?.click()}
                                className="bg-violet-600 hover:bg-violet-700 text-white text-sm px-4 py-2 rounded-lg cursor-pointer transition-all">
                                📷 Ubah Foto
                            </button>
                            <input ref={photoRef} type="file" accept="image/*" className="hidden" onChange={handlePhotoChange} />
                        </div>
                    </div>
                </div>

                {/* Profile Form */}
                <div className="bg-white rounded-2xl border border-slate-200 p-6">
                    <h2 className="text-lg font-bold text-slate-800 mb-6">Informasi Akun</h2>
                    <form onSubmit={handleSubmit} className="space-y-5">
                        {[
                            { field: 'name', label: 'Nama Lengkap', type: 'text', placeholder: 'Nama lengkap Anda' },
                            { field: 'username', label: 'Username', type: 'text', placeholder: 'username_unik' },
                            { field: 'email', label: 'Email', type: 'email', placeholder: 'email@contoh.com' },
                        ].map(({ field, label, type, placeholder }) => (
                            <div key={field}>
                                <label className="block text-sm font-medium text-slate-700 mb-1.5">{label}</label>
                                <input type={type} value={data[field]} onChange={e => setData(field, e.target.value)}
                                    className={`w-full border rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 transition-all ${errors[field] ? 'border-red-400 bg-red-50' : 'border-slate-300'}`}
                                    placeholder={placeholder} />
                                {errors[field] && <p className="text-red-500 text-xs mt-1">{errors[field]}</p>}
                            </div>
                        ))}

                        <hr className="border-slate-200" />
                        <p className="text-sm font-medium text-slate-600">Ganti Password (kosongkan jika tidak ingin ubah)</p>

                        {[
                            { field: 'password', label: 'Password Baru', placeholder: 'Min. 6 karakter' },
                            { field: 'password_confirmation', label: 'Konfirmasi Password', placeholder: 'Ulangi password baru' },
                        ].map(({ field, label, placeholder }) => (
                            <div key={field}>
                                <label className="block text-sm font-medium text-slate-700 mb-1.5">{label}</label>
                                <input type="password" value={data[field]} onChange={e => setData(field, e.target.value)}
                                    className={`w-full border rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 transition-all ${errors[field] ? 'border-red-400 bg-red-50' : 'border-slate-300'}`}
                                    placeholder={placeholder} />
                                {errors[field] && <p className="text-red-500 text-xs mt-1">{errors[field]}</p>}
                            </div>
                        ))}

                        <div className="flex justify-end pt-2">
                            <button type="submit" disabled={processing}
                                className="flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white px-6 py-2.5 rounded-lg font-medium text-sm cursor-pointer disabled:opacity-60 transition-all">
                                {processing ? <span className="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" /> : null}
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
