<?php

namespace Tests\Feature;

use App\Features\Post\Controllers\PostController;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * Memanggil PostController langsung (tanpa middleware route) untuk memastikan
 * pemeriksaan otorisasi di dalam controller itu sendiri tetap menolak non-pemilik.
 */
class PostControllerAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $intruder;
    private Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner    = User::factory()->create(['role' => 'member']);
        $this->intruder = User::factory()->create(['role' => 'member']);
        $this->post     = Post::factory()->create(['user_id' => $this->owner->id]);

        $this->actingAs($this->intruder);
    }

    public function test_destroy_aborts_with_403_for_non_owner_and_keeps_post(): void
    {
        try {
            app(PostController::class)->destroy($this->post);
            $this->fail('destroy() seharusnya melempar HttpException 403 untuk non-pemilik.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        $this->assertDatabaseHas('posts', ['id' => $this->post->id]);
    }

    public function test_publish_aborts_with_403_for_non_owner_and_keeps_status(): void
    {
        $before = $this->post->fresh()->is_published;

        try {
            app(PostController::class)->publish($this->post);
            $this->fail('publish() seharusnya melempar HttpException 403 untuk non-pemilik.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        $this->assertSame($before, $this->post->fresh()->is_published);
    }
}