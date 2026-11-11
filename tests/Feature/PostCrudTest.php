<?php

namespace Tests\Feature;

use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $member;
    private User $otherMember;

    protected function setUp(): void
    {
        parent::setUp();
        $this->member      = User::factory()->member()->create();
        $this->otherMember = User::factory()->member()->create();
    }

    // ── Dashboard Index ────────────────────────────────────────────────

    public function test_member_can_view_post_index(): void
    {
        Post::factory()->forUser($this->member)->count(3)->create();
        $response = $this->actingAs($this->member)->get(route('dashboard.posts.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($p) => $p->component('Post/Index'));
    }

    public function test_post_index_shows_only_own_posts(): void
    {
        Post::factory()->forUser($this->member)->count(2)->create();
        Post::factory()->forUser($this->otherMember)->count(3)->create();
        $response = $this->actingAs($this->member)->get(route('dashboard.posts.index'));
        $response->assertInertia(fn ($p) => $p
            ->component('Post/Index')
            ->has('posts.data', 2)
        );
    }

    public function test_post_index_has_stats(): void
    {
        Post::factory()->forUser($this->member)->published()->count(2)->create();
        Post::factory()->forUser($this->member)->draft()->count(1)->create();
        $response = $this->actingAs($this->member)->get(route('dashboard.posts.index'));
        $response->assertInertia(fn ($p) => $p
            ->has('stats.total')
            ->has('stats.published')
            ->has('stats.draft')
        );
    }

    // ── Create ─────────────────────────────────────────────────────────

    public function test_member_can_create_draft_post(): void
    {
        $response = $this->actingAs($this->member)->post(route('dashboard.posts.store'), [
            'title'        => 'My Test Post',
            'category'     => PostCategory::Teknologi->value,
            'content'      => '## Content here',
            'is_published' => false,
        ]);
        $response->assertRedirect(route('dashboard.posts.index'));
        $this->assertDatabaseHas('posts', [
            'user_id'      => $this->member->id,
            'title'        => 'My Test Post',
            'is_published' => false,
        ]);
    }

    public function test_member_can_create_published_post(): void
    {
        $this->actingAs($this->member)->post(route('dashboard.posts.store'), [
            'title'        => 'Published Post',
            'category'     => PostCategory::Bisnis->value,
            'content'      => 'Some content',
            'is_published' => true,
        ]);
        $post = Post::where('title', 'Published Post')->first();
        $this->assertTrue($post->is_published);
        $this->assertNotNull($post->published_at);
    }

    public function test_create_post_validates_required_fields(): void
    {
        $response = $this->actingAs($this->member)->post(route('dashboard.posts.store'), []);
        $response->assertSessionHasErrors(['title', 'category', 'content']);
    }

    public function test_create_post_validates_category(): void
    {
        $response = $this->actingAs($this->member)->post(route('dashboard.posts.store'), [
            'title'    => 'Test',
            'category' => 'InvalidCategory',
            'content'  => 'Content',
        ]);
        $response->assertSessionHasErrors(['category']);
    }

    public function test_slug_is_auto_generated(): void
    {
        $this->actingAs($this->member)->post(route('dashboard.posts.store'), [
            'title'    => 'My Unique Article Title',
            'category' => PostCategory::Umum->value,
            'content'  => 'Content',
        ]);
        $this->assertDatabaseHas('posts', ['slug' => 'my-unique-article-title']);
    }

    public function test_duplicate_slug_gets_suffix(): void
    {
        Post::factory()->forUser($this->member)->create(['title' => 'Same Title', 'slug' => 'same-title']);
        $this->actingAs($this->member)->post(route('dashboard.posts.store'), [
            'title'    => 'Same Title',
            'category' => PostCategory::Umum->value,
            'content'  => 'Content',
        ]);
        $this->assertDatabaseHas('posts', ['slug' => 'same-title-1']);
    }

    // ── Show ───────────────────────────────────────────────────────────

    public function test_member_can_view_own_post(): void
    {
        $post = Post::factory()->forUser($this->member)->create();
        $response = $this->actingAs($this->member)->get(route('dashboard.posts.show', $post));
        $response->assertStatus(200);
        $response->assertInertia(fn ($p) => $p->component('Post/Show'));
    }

    public function test_member_cannot_view_others_post(): void
    {
        $post = Post::factory()->forUser($this->otherMember)->create();
        $response = $this->actingAs($this->member)->get(route('dashboard.posts.show', $post));
        $response->assertStatus(403);
    }

    // ── Update ─────────────────────────────────────────────────────────

    public function test_member_can_update_own_post(): void
    {
        $post = Post::factory()->forUser($this->member)->create();
        $response = $this->actingAs($this->member)->put(route('dashboard.posts.update', $post), [
            'title'    => 'Updated Title',
            'category' => PostCategory::Opini->value,
            'content'  => 'Updated content',
        ]);
        $response->assertRedirect(route('dashboard.posts.index'));
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'Updated Title']);
    }

    public function test_member_cannot_update_others_post(): void
    {
        $post = Post::factory()->forUser($this->otherMember)->create();
        $response = $this->actingAs($this->member)->put(route('dashboard.posts.update', $post), [
            'title'    => 'Hacked',
            'category' => PostCategory::Umum->value,
            'content'  => 'Hacked',
        ]);
        $response->assertStatus(403);
    }

    // ── Delete ─────────────────────────────────────────────────────────

    public function test_member_can_delete_own_post(): void
    {
        $post = Post::factory()->forUser($this->member)->create();
        $response = $this->actingAs($this->member)->delete(route('dashboard.posts.destroy', $post));
        $response->assertRedirect(route('dashboard.posts.index'));
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_member_cannot_delete_others_post(): void
    {
        $post = Post::factory()->forUser($this->otherMember)->create();
        $response = $this->actingAs($this->member)->delete(route('dashboard.posts.destroy', $post));
        $response->assertStatus(403);
    }

    public function test_delete_also_removes_cover_image(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('cover.jpg');
        $path = $file->store('covers', 'public');
        $post = Post::factory()->forUser($this->member)->create(['cover_image_path' => $path]);

        $this->actingAs($this->member)->delete(route('dashboard.posts.destroy', $post));
        Storage::disk('public')->assertMissing($path);
    }

    // ── Publish Toggle ─────────────────────────────────────────────────

    public function test_member_can_publish_draft(): void
    {
        $post = Post::factory()->forUser($this->member)->draft()->create();
        $this->actingAs($this->member)->patch(route('dashboard.posts.publish', $post));
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'is_published' => true]);
        $this->assertNotNull($post->fresh()->published_at);
    }

    public function test_member_can_unpublish(): void
    {
        $post = Post::factory()->forUser($this->member)->published()->create();
        $this->actingAs($this->member)->patch(route('dashboard.posts.publish', $post));
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'is_published' => false]);
        $this->assertNull($post->fresh()->published_at);
    }

    // ── Cover Upload/Delete ────────────────────────────────────────────

    public function test_member_can_upload_cover(): void
    {
        Storage::fake('public');
        $post = Post::factory()->forUser($this->member)->create();
        $file = UploadedFile::fake()->image('cover.jpg', 800, 600);

        $response = $this->actingAs($this->member)->post(route('dashboard.posts.cover.upload', $post), [
            'cover' => $file,
        ]);
        $response->assertRedirect();
        $this->assertNotNull($post->fresh()->cover_image_path);
    }

    public function test_cover_upload_validates_image(): void
    {
        $post = Post::factory()->forUser($this->member)->create();
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($this->member)->post(route('dashboard.posts.cover.upload', $post), [
            'cover' => $file,
        ]);
        $response->assertSessionHasErrors(['cover']);
    }

    public function test_member_can_delete_cover(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('cover.jpg');
        $path = $file->store('covers', 'public');
        $post = Post::factory()->forUser($this->member)->create(['cover_image_path' => $path]);

        $response = $this->actingAs($this->member)->delete(route('dashboard.posts.cover.delete', $post));
        $response->assertRedirect();
        $this->assertNull($post->fresh()->cover_image_path);
        Storage::disk('public')->assertMissing($path);
    }

    // ── Guest access ───────────────────────────────────────────────────

    public function test_guest_cannot_access_dashboard(): void
    {
        $post = Post::factory()->forUser($this->member)->create();
        $this->get(route('dashboard.posts.index'))->assertRedirect(route('login'));
        $this->post(route('dashboard.posts.store'), [])->assertRedirect(route('login'));
        $this->put(route('dashboard.posts.update', $post), [])->assertRedirect(route('login'));
        $this->delete(route('dashboard.posts.destroy', $post))->assertRedirect(route('login'));
    }
}
