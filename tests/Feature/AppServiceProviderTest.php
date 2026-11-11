<?php

namespace Tests\Feature;

use App\Features\Post\Models\Post;
use App\Features\Post\Policies\PostPolicy;
use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    public function test_boot_forces_https_scheme_when_configured(): void
    {
        config(['app.force_https' => true]);

        (new AppServiceProvider($this->app))->boot();

        $this->assertStringStartsWith('https://', URL::to('/dummy'));
    }

    public function test_boot_keeps_default_scheme_when_https_not_forced(): void
    {
        config(['app.force_https' => false]);

        (new AppServiceProvider($this->app))->boot();

        $this->assertStringStartsWith('http://', URL::to('/dummy'));
    }

    public function test_boot_registers_post_policy(): void
    {
        (new AppServiceProvider($this->app))->boot();

        $this->assertInstanceOf(PostPolicy::class, Gate::getPolicyFor(Post::class));
    }
}