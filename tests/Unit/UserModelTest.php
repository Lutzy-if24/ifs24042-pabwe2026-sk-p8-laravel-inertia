<?php

namespace Tests\Unit;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    public function test_posts_relation_is_has_many_post_via_user_id(): void
    {
        $user = User::factory()->make(['id' => 7]);

        $relation = $user->posts();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertInstanceOf(Post::class, $relation->getRelated());
        $this->assertSame('user_id', $relation->getForeignKeyName());
        $this->assertSame('posts.user_id', $relation->getQualifiedForeignKeyName());
        $this->assertSame(7, $relation->getParentKey());
    }

    public function test_is_member_is_true_only_for_member_role(): void
    {
        $this->assertTrue(User::factory()->make(['role' => 'member'])->isMember());
        $this->assertFalse(User::factory()->make(['role' => 'admin'])->isMember());
    }

    public function test_is_admin_is_true_only_for_admin_role(): void
    {
        $this->assertTrue(User::factory()->make(['role' => 'admin'])->isAdmin());
        $this->assertFalse(User::factory()->make(['role' => 'member'])->isAdmin());
    }
}