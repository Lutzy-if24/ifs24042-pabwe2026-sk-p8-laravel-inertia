<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->member()->create([
            'name'     => 'Original Name',
            'username' => 'original_user',
            'email'    => 'original@example.com',
        ]);
    }

    public function test_profile_edit_page_renders(): void
    {
        $response = $this->actingAs($this->user)->get(route('profile.edit'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($p) => $p->component('Profile/Edit'));
    }

    public function test_user_can_update_name_and_email(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.update'), [
            'name'     => 'Updated Name',
            'username' => 'original_user',
            'email'    => 'original@example.com',
        ]);
        $response->assertRedirect(route('profile.edit'));
        $this->assertDatabaseHas('users', ['id' => $this->user->id, 'name' => 'Updated Name']);
    }

    public function test_user_can_change_username(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.update'), [
            'name'     => 'Original Name',
            'username' => 'new_username',
            'email'    => 'original@example.com',
        ]);
        $response->assertRedirect(route('profile.edit'));
        $this->assertDatabaseHas('users', ['id' => $this->user->id, 'username' => 'new_username']);
    }

    public function test_user_cannot_change_username_to_existing_one(): void
    {
        User::factory()->create(['username' => 'taken_name']);
        $response = $this->actingAs($this->user)->put(route('profile.update'), [
            'name'     => 'Original Name',
            'username' => 'taken_name',
            'email'    => 'original@example.com',
        ]);
        $response->assertSessionHasErrors(['username']);
    }

    public function test_user_can_keep_own_username(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.update'), [
            'name'     => 'Original Name',
            'username' => 'original_user',  // own current username
            'email'    => 'original@example.com',
        ]);
        $response->assertRedirect(route('profile.edit'));
    }

    public function test_user_can_change_password(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.update'), [
            'name'                  => 'Original Name',
            'username'              => 'original_user',
            'email'                 => 'original@example.com',
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);
        $response->assertRedirect(route('profile.edit'));
    }

    public function test_user_can_upload_profile_photo(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $response = $this->actingAs($this->user)->post(route('profile.photo'), ['photo' => $file]);
        $response->assertRedirect(route('profile.edit'));
        $this->assertNotNull($this->user->fresh()->profile_photo_path);
        Storage::disk('public')->assertExists($this->user->fresh()->profile_photo_path);
    }

    public function test_photo_upload_validates_image(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);
        $response = $this->actingAs($this->user)->post(route('profile.photo'), ['photo' => $file]);
        $response->assertSessionHasErrors(['photo']);
    }

    public function test_photo_upload_validates_max_size(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('big.jpg')->size(2048); // 2MB > 1024KB limit
        $response = $this->actingAs($this->user)->post(route('profile.photo'), ['photo' => $file]);
        $response->assertSessionHasErrors(['photo']);
    }

    public function test_new_photo_replaces_old_photo(): void
    {
        Storage::fake('public');
        $oldFile = UploadedFile::fake()->image('old.jpg');
        $oldPath = $oldFile->store('profiles', 'public');
        $this->user->update(['profile_photo_path' => $oldPath]);

        $newFile = UploadedFile::fake()->image('new.jpg', 200, 200);
        $this->actingAs($this->user)->post(route('profile.photo'), ['photo' => $newFile]);

        Storage::disk('public')->assertMissing($oldPath);
    }

    public function test_guest_cannot_access_profile(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
        $this->put(route('profile.update'), [])->assertRedirect(route('login'));
    }
}
