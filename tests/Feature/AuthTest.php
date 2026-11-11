<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // ── Register ───────────────────────────────────────────────────────

    public function test_register_page_renders(): void
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Auth/Register'));
    }

    public function test_user_can_register(): void
    {
        $response = $this->post(route('register.post'), [
            'name'                  => 'Test User',
            'username'              => 'testuser',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard.posts.index'));
        $this->assertDatabaseHas('users', ['username' => 'testuser', 'role' => 'member']);
        $this->assertAuthenticated();
    }

    public function test_register_validates_required_fields(): void
    {
        $response = $this->post(route('register.post'), []);
        $response->assertSessionHasErrors(['name', 'username', 'email', 'password']);
    }

    public function test_register_validates_unique_username(): void
    {
        User::factory()->create(['username' => 'taken']);
        $response = $this->post(route('register.post'), [
            'name'                  => 'Another User',
            'username'              => 'taken',
            'email'                 => 'another@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertSessionHasErrors(['username']);
    }

    public function test_register_validates_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $response = $this->post(route('register.post'), [
            'name'                  => 'Another User',
            'username'              => 'newuser',
            'email'                 => 'taken@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertSessionHasErrors(['email']);
    }

    public function test_register_validates_password_confirmation(): void
    {
        $response = $this->post(route('register.post'), [
            'name'                  => 'User',
            'username'              => 'user1',
            'email'                 => 'user@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'different',
        ]);
        $response->assertSessionHasErrors(['password']);
    }

    // ── Login ──────────────────────────────────────────────────────────

    public function test_login_page_renders(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Auth/Login'));
    }

    public function test_member_can_login_with_username(): void
    {
        $user = User::factory()->create(['username' => 'member1', 'role' => 'member']);
        $response = $this->post(route('login.post'), ['username' => 'member1', 'password' => 'password']);
        $response->assertRedirect(route('dashboard.posts.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_login_redirects_to_admin(): void
    {
        $admin = User::factory()->admin()->create(['username' => 'adminuser']);
        $response = $this->post(route('login.post'), ['username' => 'adminuser', 'password' => 'password']);
        $response->assertRedirect(route('admin.users.index'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['username' => 'user2']);
        $response = $this->post(route('login.post'), ['username' => 'user2', 'password' => 'wrong']);
        $response->assertSessionHasErrors(['username']);
    }

    public function test_login_validates_required_fields(): void
    {
        $response = $this->post(route('login.post'), []);
        $response->assertSessionHasErrors(['username', 'password']);
    }

    // ── Logout ─────────────────────────────────────────────────────────

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->post(route('logout'));
        $response->assertRedirect(route('public.index'));
        $this->assertGuest();
    }

    // ── Guest redirect ─────────────────────────────────────────────────

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $response = $this->get(route('dashboard.posts.index'));
        $response->assertRedirect(route('login'));
    }

    // ── Authenticated redirect ─────────────────────────────────────────

    public function test_authenticated_user_redirected_from_login(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('login'));
        $response->assertRedirect();
    }
}
