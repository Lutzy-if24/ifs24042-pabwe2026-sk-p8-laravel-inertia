<?php

namespace Tests\Feature;

use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPostTest extends TestCase
{
    use RefreshDatabase;

    private User $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->member = User::factory()->member()->create();
    }

    public function test_public_index_renders(): void
    {
        $response = $this->get(route('public.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($p) => $p->component('Public/PostIndex'));
    }

    public function test_public_articles_renders(): void
    {
        $response = $this->get(route('public.articles'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($p) => $p->component('Public/PostIndex'));
    }

    public function test_public_index_shows_only_published_posts(): void
    {
        Post::factory()->forUser($this->member)->published()->count(3)->create();
        Post::factory()->forUser($this->member)->draft()->count(2)->create();

        $response = $this->get(route('public.articles'));
        $response->assertInertia(fn ($p) => $p->has('posts.data', 3));
    }

    public function test_public_index_search_by_title(): void
    {
        Post::factory()->forUser($this->member)->published()->create(['title' => 'Laravel Tutorial', 'slug' => 'laravel-tutorial']);
        Post::factory()->forUser($this->member)->published()->create(['title' => 'React Guide', 'slug' => 'react-guide']);

        $response = $this->get(route('public.articles', ['search' => 'Laravel']));
        $response->assertInertia(fn ($p) => $p->has('posts.data', 1));
    }

    public function test_public_index_filter_by_category(): void
    {
        Post::factory()->forUser($this->member)->published()->create([
            'category' => PostCategory::Teknologi->value,
            'slug'     => 'tech-post',
        ]);
        Post::factory()->forUser($this->member)->published()->create([
            'category' => PostCategory::Bisnis->value,
            'slug'     => 'bisnis-post',
        ]);

        $response = $this->get(route('public.articles', ['category' => PostCategory::Teknologi->value]));
        $response->assertInertia(fn ($p) => $p->has('posts.data', 1));
    }

    public function test_public_post_show_renders_for_published(): void
    {
        $post = Post::factory()->forUser($this->member)->published()->create();
        $response = $this->get(route('public.articles.show', $post->slug));
        $response->assertStatus(200);
        $response->assertInertia(fn ($p) => $p->component('Public/PostShow'));
    }

    public function test_draft_post_returns_404_on_public(): void
    {
        $post = Post::factory()->forUser($this->member)->draft()->create();
        $response = $this->get(route('public.articles.show', $post->slug));
        $response->assertStatus(404);
    }

    public function test_nonexistent_slug_returns_404(): void
    {
        $response = $this->get(route('public.articles.show', 'nonexistent-slug'));
        $response->assertStatus(404);
    }

    public function test_public_index_has_categories(): void
    {
        $response = $this->get(route('public.articles'));
        $response->assertInertia(fn ($p) => $p->has('categories'));
    }
}
