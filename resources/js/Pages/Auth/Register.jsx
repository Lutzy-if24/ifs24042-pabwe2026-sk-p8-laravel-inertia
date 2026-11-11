import { useForm, Link } from '@inertiajs/react';

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        username: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    function handleSubmit(e) {
        e.preventDefault();
        post('/register');
    }

    return (
        <div className="min-h-screen bg-gradient-to-br from-indigo-950 via-violet-900 to-purple-900 flex items-center justify-center p-4">
            <div className="absolute inset-0 overflow-hidden pointer-events-none">
                <div className="absolute -top-40 -left-40 w-80 h-80 bg-indigo-500/20 rounded-full blur-3xl" />
                <div className="absolute -bottom-40 -right-40 w-80 h-80 bg-purple-500/20 rounded-full blur-3xl" />
            </div>

            <div className="w-full max-w-md relative">
                <div className="text-center mb-8">
                    <Link href="/" className="inline-flex items-center gap-3">
                        <div className="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-lg">
                            <span className="text-violet-700 font-black text-2xl">B</span>
                        </div>
                        <div className="text-left">
                            <div className="text-white font-bold text-xl">BlogVerse Pro</div>
                            <div className="text-violet-300 text-sm">Platform Publikasi Artikel</div>
                        </div>
                    </Link>
                </div>

                <div className="bg-white/10 backdrop-blur-xl rounded-2xl p-8 border border-white/20 shadow-2xl">
                    <h1 className="text-white text-2xl font-bold mb-1">Buat Akun Baru</h1>
                    <p className="text-violet-300 text-sm mb-6">Mulai perjalanan menulis Anda</p>

                    <form onSubmit={handleSubmit} className="space-y-4">
                        {[
                            { id: 'reg-name', field: 'name', label: 'Nama Lengkap', type: 'text', placeholder: 'Nama lengkap Anda', auto: 'name' },
                            { id: 'reg-username', field: 'username', label: 'Username', type: 'text', placeholder: 'username_unik', auto: 'username' },
                            { id: 'reg-email', field: 'email', label: 'Email', type: 'email', placeholder: 'email@contoh.com', auto: 'email' },
                            { id: 'reg-password', field: 'password', label: 'Password', type: 'password', placeholder: 'Min. 6 karakter', auto: 'new-password' },
                            { id: 'reg-password-confirm', field: 'password_confirmation', label: 'Konfirmasi Password', type: 'password', placeholder: 'Ulangi password', auto: 'new-password' },
                        ].map(({ id, field, label, type, placeholder, auto }) => (
                            <div key={field}>
                                <label htmlFor={id} className="block text-sm font-medium text-violet-200 mb-1.5">{label}</label>
                                <input
                                    id={id}
                                    type={type}
                                    value={data[field]}
                                    onChange={e => setData(field, e.target.value)}
                                    className={`w-full bg-white/10 border rounded-lg px-4 py-2.5 text-white placeholder-violet-400 focus:outline-none focus:ring-2 focus:ring-violet-400 transition-all ${errors[field] ? 'border-red-400' : 'border-white/20'}`}
                                    placeholder={placeholder}
                                    autoComplete={auto}
                                />
                                {errors[field] && <p className="text-red-400 text-xs mt-1">{errors[field]}</p>}
                            </div>
                        ))}

                        <button
                            id="register-submit"
                            type="submit"
                            disabled={processing}
                            className="w-full bg-indigo-500 hover:bg-indigo-400 text-white font-semibold py-3 rounded-lg transition-all duration-200 mt-2 cursor-pointer disabled:opacity-60 flex items-center justify-center gap-2"
                        >
                            {processing ? (
                                <>
                                    <span className="inline-block w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" />
                                    Mendaftar...
                                </>
                            ) : 'Daftar Sekarang'}
                        </button>
                    </form>

                    <p className="text-center text-violet-300 text-sm mt-6">
                        Sudah punya akun?{' '}
                        <Link href="/login" className="text-violet-200 hover:text-white font-medium underline">
                            Masuk di sini
                        </Link>
                    </p>
                </div>

                <p className="text-center text-violet-400 text-xs mt-4">
                    <Link href="/" className="hover:text-violet-200 transition-colors">
                        ← Kembali ke Beranda
                    </Link>
                </p>
            </div>
        </div>
    );
}
