<?php

namespace Tests\Feature;

use App\Features\Admin\Services\AdminUserService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserServiceTest extends TestCase
{
    use RefreshDatabase;

    private AdminUserService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(AdminUserService::class);
    }

    public function test_find_user_by_id_returns_the_matching_user(): void
    {
        User::factory()->create();
        $user = User::factory()->create();

        $found = $this->service->findUserById($user->id);

        $this->assertInstanceOf(User::class, $found);
        $this->assertTrue($found->is($user));
        $this->assertSame($user->email, $found->email);
    }

    public function test_find_user_by_id_returns_null_when_user_does_not_exist(): void
    {
        $user = User::factory()->create();
        $id = $user->id;
        $user->delete();

        $this->assertNull($this->service->findUserById($id));
    }
}