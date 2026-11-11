<?php

namespace Tests\Feature;

use App\Features\Post\Models\Post;
use App\Features\Post\Services\PostService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostServiceTest extends TestCase
{
    use RefreshDatabase;

    private PostService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PostService::class);
    }

    public function test_update_post_publishing_a_draft_sets_published_at(): void
    {
        $post = Post::factory()->create([
            'user_id'      => User::factory()->create()->id,
            'slug'         => 'draf-jadi-terbit',
            'is_published' => false,
            'published_at' => null,
        ]);

        $updated = $this->service->updatePost($post, [
            'title'        => 'Judul Baru',
            'is_published' => true,
        ]);

        $this->assertTrue($updated->is_published);
        $this->assertNotNull($updated->published_at);
        $this->assertSame('Judul Baru', $updated->title);
    }

    public function test_update_post_unpublishing_clears_published_at(): void
    {
        $post = Post::factory()->create([
            'user_id'      => User::factory()->create()->id,
            'slug'         => 'terbit-jadi-draf',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $updated = $this->service->updatePost($post, [
            'is_published' => false,
        ]);

        $this->assertFalse($updated->is_published);
        $this->assertNull($updated->published_at);
    }

    public function test_update_post_keeps_published_at_when_already_published(): void
    {
        $publishedAt = now()->subDays(3)->startOfSecond();

        $post = Post::factory()->create([
            'user_id'      => User::factory()->create()->id,
            'slug'         => 'tetap-terbit',
            'is_published' => true,
            'published_at' => $publishedAt,
        ]);

        $updated = $this->service->updatePost($post, [
            'title'        => 'Judul Diubah',
            'is_published' => true,
        ]);

        $this->assertTrue($updated->is_published);
        $this->assertSame('Judul Diubah', $updated->title);
        $this->assertTrue($updated->published_at->equalTo($publishedAt));
    }

    public function test_upload_cover_replaces_and_deletes_old_cover(): void
    {
        Storage::fake('public');

        $post = Post::factory()->create([
            'user_id' => User::factory()->create()->id,
            'slug'    => 'ganti-cover',
        ]);

        $first   = $this->service->uploadCover($post, UploadedFile::fake()->image('lama.jpg'));
        $oldPath = $first->cover_image_path;
        Storage::disk('public')->assertExists($oldPath);

        $second = $this->service->uploadCover($first, UploadedFile::fake()->image('baru.jpg'));

        $this->assertNotSame($oldPath, $second->cover_image_path);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($second->cover_image_path);
    }
}