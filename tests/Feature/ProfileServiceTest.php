<?php

namespace Tests\Feature;

use App\Features\Profile\Services\ProfileService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProfileService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->service = app(ProfileService::class);
    }

    public function test_delete_photo_removes_file_and_clears_path(): void
    {
        $path = UploadedFile::fake()->image('avatar.jpg')->store('profiles', 'public');
        $user = User::factory()->create(['profile_photo_path' => $path]);

        Storage::disk('public')->assertExists($path);

        $result = $this->service->deletePhoto($user);

        Storage::disk('public')->assertMissing($path);
        $this->assertTrue($result->is($user));
        $this->assertNull($result->profile_photo_path);
        $this->assertDatabaseHas('users', [
            'id'                 => $user->id,
            'profile_photo_path' => null,
        ]);
    }

    public function test_delete_photo_without_photo_changes_nothing(): void
    {
        $user = User::factory()->create(['profile_photo_path' => null]);
        Storage::disk('public')->put('profiles/other.jpg', 'dummy');

        $result = $this->service->deletePhoto($user);

        $this->assertTrue($result->is($user));
        $this->assertNull($result->profile_photo_path);
        Storage::disk('public')->assertExists('profiles/other.jpg');
    }
}