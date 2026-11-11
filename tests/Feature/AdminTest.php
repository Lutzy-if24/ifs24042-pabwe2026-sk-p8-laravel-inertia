<?php

namespace Tests\Feature;

use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin  = User::factory()->admin()->create();
        $this->member = User::factory()->member()->create();
    }

    // ── Access Control ─────────────────────────────────────────────────

    public function test_admin_can_access_user_list(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($p) => $p->component('Admin/UserList'));
    }

    public function test_member_cannot_access_admin_routes(): void
    {
        $this->actingAs($this->member)->get(route('admin.users.index'))->assertStatus(403);
        $this->actingAs($this->member)->get(route('admin.posts.index'))->assertStatus(403);
    }

    public function test_guest_cannot_access_admin_routes(): void
    {
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
    }

    // ── User Management ────────────────────────────────────────────────

    public function test_admin_can_view_all_users(): void
    {
        User::factory()->count(3)->member()->create();
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $response->assertInertia(fn ($p) => $p->has('users.data'));
    }

    public function test_admin_can_search_users(): void
    {
        User::factory()->member()->create(['name' => 'SearchableUserName']);
        User::factory()->member()->create(['name' => 'Other User']);
        $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['search' => 'SearchableUserName']));
        $response->assertInertia(fn ($p) => $p->has('users.data', 1));
    }

    public function test_admin_can_change_user_role_to_admin(): void
    {
        $response = $this->actingAs($this->admin)->patch(route('admin.users.role', $this->member), ['role' => 'admin']);
        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['id' => $this->member->id, 'role' => 'admin']);
    }

    public function test_admin_role_change_validates_role_field(): void
    {
        $response = $this->actingAs($this->admin)->patch(route('admin.users.role', $this->member), ['role' => 'superuser']);
        $response->assertSessionHasErrors(['role']);
    }

    public function test_admin_can_delete_other_user(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->member));
        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $this->member->id]);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin));
        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    // ── Post Management ────────────────────────────────────────────────

    public function test_admin_can_view_all_posts(): void
    {
        Post::factory()->forUser($this->member)->count(3)->create();
        $response = $this->actingAs($this->admin)->get(route('admin.posts.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($p) => $p
            ->component('Admin/PostList')
            ->has('posts.data', 3)
        );
    }

    public function test_admin_can_view_user_posts(): void
    {
        Post::factory()->forUser($this->member)->count(2)->create();
        $response = $this->actingAs($this->admin)->get(route('admin.users.posts', $this->member));
        $response->assertStatus(200);
        $response->assertInertia(fn ($p) => $p
            ->component('Admin/UserPostList')
            ->has('posts.data', 2)
        );
    }

    public function test_admin_can_filter_posts_by_category(): void
    {
        Post::factory()->forUser($this->member)->create(['category' => PostCategory::Teknologi->value]);
        Post::factory()->forUser($this->member)->create(['category' => PostCategory::Bisnis->value]);
        $response = $this->actingAs($this->admin)->get(route('admin.posts.index', ['category' => PostCategory::Teknologi->value]));
        $response->assertInertia(fn ($p) => $p->has('posts.data', 1));
    }

    public function test_admin_can_search_posts(): void
    {
        Post::factory()->forUser($this->member)->create(['title' => 'Admin Search Target']);
        Post::factory()->forUser($this->member)->create(['title' => 'Other Post']);
        $response = $this->actingAs($this->admin)->get(route('admin.posts.index', ['search' => 'Admin Search Target']));
        $response->assertInertia(fn ($p) => $p->has('posts.data', 1));
    }
}
