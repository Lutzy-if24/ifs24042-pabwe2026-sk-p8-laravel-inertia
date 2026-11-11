<?php

namespace Database\Seeders;

use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        $admin = User::create([
            'name'     => 'Administrator',
            'username' => 'admin',
            'email'    => 'admin@blogverse.pro',
            'password' => Hash::make('admin'),
            'role'     => 'admin',
        ]);

        // Member user
        $member = User::create([
            'name'     => 'Member User',
            'username' => 'member',
            'email'    => 'member@blogverse.pro',
            'password' => Hash::make('member'),
            'role'     => 'member',
        ]);

        // Posts for admin – mix published & draft
        $adminPublishedPosts = [
            [
                'title'        => 'Memulai Perjalanan dengan Laravel 13',
                'category'     => PostCategory::Teknologi->value,
                'excerpt'      => 'Laravel 13 membawa banyak fitur baru yang memudahkan pengembangan aplikasi web modern. Mari kita jelajahi bersama.',
                'content'      => "## Pengenalan Laravel 13\n\nLaravel 13 adalah versi terbaru dari framework PHP yang paling populer di dunia. Dengan berbagai peningkatan performa dan fitur baru, Laravel terus menjadi pilihan utama para developer.\n\n### Fitur Unggulan\n\n- **Inertia.js v2**: Integrasi yang lebih mulus dengan React dan Vue\n- **Eloquent Improvements**: Query lebih cepat dan ekspresif\n- **Queue Enhancements**: Penanganan job yang lebih andal\n\nDengan ekosistem yang kaya dan komunitas yang besar, Laravel 13 siap membantu Anda membangun aplikasi skala enterprise.",
                'is_published' => true,
                'published_at' => now()->subDays(5),
            ],
            [
                'title'        => 'Tips Produktivitas untuk Developer Modern',
                'category'     => PostCategory::GayaHidup->value,
                'excerpt'      => 'Menjadi developer yang produktif bukan hanya tentang menulis kode lebih cepat, tapi juga tentang bekerja lebih cerdas.',
                'content'      => "## Produktivitas sebagai Developer\n\nProduktivitas bukan hanya soal menghasilkan lebih banyak kode dalam waktu singkat. Ini tentang membuat keputusan yang tepat dan bekerja secara berkelanjutan.\n\n### Tips Utama\n\n1. **Deep Work**: Blokir waktu 2-3 jam tanpa distraksi\n2. **Pomodoro Technique**: 25 menit kerja, 5 menit istirahat\n3. **Code Review Rutin**: Belajar dari feedback rekan tim\n\nIngat, kualitas lebih penting dari kuantitas.",
                'is_published' => true,
                'published_at' => now()->subDays(10),
            ],
        ];

        foreach ($adminPublishedPosts as $postData) {
            Post::create(array_merge($postData, ['user_id' => $admin->id]));
        }

        // Draft post for admin
        Post::create([
            'user_id'      => $admin->id,
            'title'        => 'Arsitektur Microservices: Kapan Harus Digunakan?',
            'category'     => PostCategory::Teknologi->value,
            'excerpt'      => 'Microservices bukan silver bullet. Artikel ini membahas kapan dan kenapa Anda harus (atau tidak harus) menggunakannya.',
            'content'      => "## Microservices vs Monolith\n\nBanyak tim development tergoda untuk langsung mengadopsi arsitektur microservices...\n\n*(Draft - belum selesai)*",
            'is_published' => false,
            'published_at' => null,
        ]);

        // Posts for member – mix published & draft
        $memberPublishedPosts = [
            [
                'title'        => 'Belajar React dari Nol: Panduan Lengkap 2026',
                'category'     => PostCategory::Pendidikan->value,
                'excerpt'      => 'React adalah library JavaScript yang paling banyak digunakan untuk membangun antarmuka pengguna. Pelajari dari dasar di sini.',
                'content'      => "## React untuk Pemula\n\nReact dikembangkan oleh Meta dan telah menjadi standar industri untuk frontend development.\n\n### Konsep Dasar\n\n- **Components**: Building blocks UI yang reusable\n- **Props**: Data yang dikirim dari parent ke child\n- **State**: Data internal komponen\n- **Hooks**: Fitur modern React untuk stateful logic\n\n```javascript\nfunction HelloWorld() {\n  return <h1>Hello, World!</h1>;\n}\n```\n\nMulailah dengan proyek kecil dan tingkatkan kompleksitas secara bertahap.",
                'is_published' => true,
                'published_at' => now()->subDays(3),
            ],
            [
                'title'        => 'Strategi Membangun Karir di Bidang Teknologi',
                'category'     => PostCategory::Bisnis->value,
                'excerpt'      => 'Industri teknologi terus berkembang pesat. Bagaimana cara membangun karir yang sustainable dan memuaskan?',
                'content'      => "## Karir di Dunia Tech\n\nDunia teknologi menawarkan banyak peluang, namun juga sangat kompetitif.\n\n### Langkah-langkah Strategis\n\n1. **Kuasai Fundamental**: Algoritma, struktur data, dan sistem design\n2. **Bangun Portfolio**: GitHub yang aktif dan proyek nyata\n3. **Networking**: Ikuti komunitas developer lokal dan global\n4. **Soft Skills**: Komunikasi dan kolaborasi sama pentingnya dengan coding\n\nIngat bahwa karir yang baik dibangun selama bertahun-tahun, bukan dalam semalam.",
                'is_published' => true,
                'published_at' => now()->subDays(7),
            ],
            [
                'title'        => 'Opini: Apakah AI Akan Menggantikan Developer?',
                'category'     => PostCategory::Opini->value,
                'excerpt'      => 'Dengan kemunculan tools AI seperti GitHub Copilot dan ChatGPT, banyak yang bertanya-tanya: apakah developer masih dibutuhkan?',
                'content'      => "## AI dan Masa Depan Developer\n\nPertanyaan ini sering muncul di berbagai forum dan diskusi tech.\n\n### Perspektif Saya\n\nAI adalah alat, bukan pengganti. Sama seperti calculator tidak menggantikan matematikawan, AI coding tools tidak akan menggantikan developer yang berpengalaman.\n\n**Yang akan berubah:**\n- Produktivitas individual meningkat drastis\n- Pekerjaan repetitif akan terotomatisasi\n- Demand untuk high-level thinking justru meningkat\n\nFokus pada problem-solving dan system design, bukan syntax.",
                'is_published' => true,
                'published_at' => now()->subDays(1),
            ],
        ];

        foreach ($memberPublishedPosts as $postData) {
            Post::create(array_merge($postData, ['user_id' => $member->id]));
        }

        // Draft posts for member
        Post::create([
            'user_id'      => $member->id,
            'title'        => 'Cara Deploy Aplikasi Laravel ke VPS',
            'category'     => PostCategory::Teknologi->value,
            'excerpt'      => 'Panduan lengkap deploy aplikasi Laravel ke VPS dengan Nginx, PHP-FPM, dan PostgreSQL.',
            'content'      => "## Deploy Laravel ke VPS\n\n*(Sedang dalam pengerjaan...)*",
            'is_published' => false,
            'published_at' => null,
        ]);

        Post::create([
            'user_id'      => $member->id,
            'title'        => 'Review: Framework CSS Terbaik 2026',
            'category'     => PostCategory::Umum->value,
            'excerpt'      => 'Dari Tailwind sampai Bootstrap, mana yang paling cocok untuk proyek Anda?',
            'content'      => "## Perbandingan Framework CSS\n\n*(Draft - belum selesai)*",
            'is_published' => false,
            'published_at' => null,
        ]);
    }
}
