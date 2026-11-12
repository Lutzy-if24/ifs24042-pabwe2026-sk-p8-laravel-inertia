import { useForm, Link } from '@inertiajs/react';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        username: '',
        password: '',
        remember: false,
    });

    function handleSubmit(e) {
        e.preventDefault();
        post('/login');
    }

    return (
        <main className="min-h-screen bg-gradient-to-br from-violet-950 via-violet-900 to-indigo-900 flex items-center justify-center p-4">
            <div className="absolute inset-0 overflow-hidden pointer-events-none">
                <div className="absolute -top-40 -right-40 w-80 h-80 bg-violet-500/20 rounded-full blur-3xl" />
                <div className="absolute -bottom-40 -left-40 w-80 h-80 bg-indigo-500/20 rounded-full blur-3xl" />
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
                    <h1 className="text-white text-2xl font-bold mb-1">Masuk ke Akun</h1>
                    <p className="text-violet-300 text-sm mb-6">Selamat datang kembali!</p>

                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div>
                            <label htmlFor="login-username" className="block text-sm font-medium text-violet-200 mb-1.5">
                                Username
                            </label>
                            <input
                                id="login-username"
                                type="text"
                                value={data.username}
                                onChange={e => setData('username', e.target.value)}
                                className={`w-full bg-white/10 border rounded-lg px-4 py-2.5 text-white placeholder-violet-400 focus:outline-none focus:ring-2 focus:ring-violet-400 transition-all ${errors.username ? 'border-red-400' : 'border-white/20'}`}
                                placeholder="Masukkan username"
                                autoComplete="username"
                                autoFocus
                            />
                            {errors.username && <p className="text-red-400 text-xs mt-1">{errors.username}</p>}
                        </div>

                        <div>
                            <label htmlFor="login-password" className="block text-sm font-medium text-violet-200 mb-1.5">
                                Password
                            </label>
                            <input
                                id="login-password"
                                type="password"
                                value={data.password}
                                onChange={e => setData('password', e.target.value)}
                                className={`w-full bg-white/10 border rounded-lg px-4 py-2.5 text-white placeholder-violet-400 focus:outline-none focus:ring-2 focus:ring-violet-400 transition-all ${errors.password ? 'border-red-400' : 'border-white/20'}`}
                                placeholder="Masukkan password"
                                autoComplete="current-password"
                            />
                            {errors.password && <p className="text-red-400 text-xs mt-1">{errors.password}</p>}
                        </div>

                        <div className="flex items-center gap-2">
                            <input
                                id="remember"
                                type="checkbox"
                                checked={data.remember}
                                onChange={e => setData('remember', e.target.checked)}
                                className="w-4 h-4 rounded accent-violet-500 cursor-pointer"
                            />
                            <label htmlFor="remember" className="text-violet-200 text-sm cursor-pointer">Ingat saya</label>
                        </div>

                        <button
                            id="login-submit"
                            type="submit"
                            disabled={processing}
                            className="w-full bg-violet-600 hover:bg-violet-700 text-white font-semibold py-3 rounded-lg transition-all duration-200 mt-2 cursor-pointer disabled:opacity-60 flex items-center justify-center gap-2"
                        >
                            {processing ? (
                                <>
                                    <span className="inline-block w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" />
                                    Memproses...
                                </>
                            ) : 'Masuk'}
                        </button>
                    </form>

                    <p className="text-center text-violet-300 text-sm mt-6">
                        Belum punya akun?{' '}
                        <Link href="/register" className="text-violet-200 hover:text-white font-medium underline">
                            Daftar sekarang
                        </Link>
                    </p>
                </div>

                <p className="text-center text-violet-400 text-xs mt-4">
                    <Link href="/" className="hover:text-violet-200 transition-colors">
                        ← Kembali ke Beranda
                    </Link>
                </p>
            </div>
        </main>
    );
}