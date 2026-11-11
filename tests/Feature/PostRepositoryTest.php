<?php

namespace Tests\Feature;

use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PostRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(PostRepositoryInterface::class);
    }

    public function test_all_for_user_filters_by_search_in_title_and_excerpt(): void
    {
        $user  = User::factory()->create();
        $other = User::factory()->create();

        Post::factory()->create([
            'user_id' => $user->id, 'slug' => 'belajar-laravel',
            'title' => 'Belajar Laravel', 'excerpt' => 'ringkasan biasa',
        ]);
        Post::factory()->create([
            'user_id' => $user->id, 'slug' => 'resep-nasi',
            'title' => 'Resep Nasi', 'excerpt' => 'tentang memasak',
        ]);
        Post::factory()->create([
            'user_id' => $user->id, 'slug' => 'catatan-lain',
            'title' => 'Catatan Lain', 'excerpt' => 'membahas LARAVEL di sini',
        ]);
        Post::factory()->create([
            'user_id' => $other->id, 'slug' => 'laravel-orang-lain',
            'title' => 'Laravel Orang Lain', 'excerpt' => 'bukan milik user',
        ]);

        $result = $this->repository->allForUser($user->id, ['search' => 'laravel']);

        $titles = $result->getCollection()->pluck('title')->all();

        $this->assertCount(2, $titles);
        $this->assertContains('Belajar Laravel', $titles);
        $this->assertContains('Catatan Lain', $titles);
        $this->assertNotContains('Laravel Orang Lain', $titles);
    }

    public function test_all_for_user_filters_by_published_status(): void
    {
        $user = User::factory()->create();

        Post::factory()->create([
            'user_id' => $user->id, 'slug' => 'artikel-terbit',
            'title' => 'Artikel Terbit', 'is_published' => true,
        ]);
        Post::factory()->create([
            'user_id' => $user->id, 'slug' => 'artikel-draf',
            'title' => 'Artikel Draf', 'is_published' => false,
        ]);

        $published = $this->repository->allForUser($user->id, ['is_published' => '1']);
        $drafts    = $this->repository->allForUser($user->id, ['is_published' => '0']);
        $all       = $this->repository->allForUser($user->id, ['is_published' => '']);

        $this->assertSame(['Artikel Terbit'], $published->getCollection()->pluck('title')->all());
        $this->assertSame(['Artikel Draf'], $drafts->getCollection()->pluck('title')->all());
        $this->assertCount(2, $all->items());
    }

    public function test_all_for_user_admin_filters_by_search_and_user(): void
    {
        $user  = User::factory()->create();
        $other = User::factory()->create();

        Post::factory()->create([
            'user_id' => $user->id, 'slug' => 'panduan-react',
            'title' => 'Panduan React',
        ]);
        Post::factory()->create([
            'user_id' => $user->id, 'slug' => 'panduan-vue',
            'title' => 'Panduan Vue',
        ]);
        Post::factory()->create([
            'user_id' => $other->id, 'slug' => 'react-milik-lain',
            'title' => 'React Milik Lain',
        ]);

        $result = $this->repository->allForUserAdmin($user->id, ['search' => 'react']);

        $this->assertSame(['Panduan React'], $result->getCollection()->pluck('title')->all());
    }

    public function test_find_by_id_returns_post_or_null(): void
    {
        $post = Post::factory()->create(['slug' => 'cari-lewat-id']);

        $found = $this->repository->findById($post->id);

        $this->assertNotNull($found);
        $this->assertSame($post->id, $found->id);
        $this->assertNull($this->repository->findById(999999));
    }

    public function test_find_by_slug_returns_post_or_null(): void
    {
        $post = Post::factory()->create(['slug' => 'cari-lewat-slug']);

        $found = $this->repository->findBySlug('cari-lewat-slug');

        $this->assertNotNull($found);
        $this->assertSame($post->id, $found->id);
        $this->assertNull($this->repository->findBySlug('slug-tidak-ada'));
    }
}