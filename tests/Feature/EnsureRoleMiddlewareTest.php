<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureRoleMiddlewareTest extends TestCase
{
    private function requestFor(?User $user): Request
    {
        $request = Request::create('/dummy');
        $request->setUserResolver(fn () => $user);

        return $request;
    }

    public function test_guest_is_redirected_to_login_without_calling_next(): void
    {
        $called = false;
        $next = function () use (&$called) {
            $called = true;

            return new Response('ok');
        };

        $response = (new EnsureRole)->handle($this->requestFor(null), $next, 'admin');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route('login'), $response->getTargetUrl());
        $this->assertFalse($called);
    }

    public function test_user_with_wrong_role_gets_403(): void
    {
        $user = User::factory()->make(['role' => 'member']);
        $called = false;
        $next = function () use (&$called) {
            $called = true;

            return new Response('ok');
        };

        try {
            (new EnsureRole)->handle($this->requestFor($user), $next, 'admin');
            $this->fail('Middleware seharusnya melempar HttpException 403.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
            $this->assertSame('Akses ditolak.', $e->getMessage());
        }

        $this->assertFalse($called);
    }

    public function test_user_with_allowed_role_passes_to_next(): void
    {
        $user = User::factory()->make(['role' => 'admin']);
        $next = fn () => new Response('lolos');

        $response = (new EnsureRole)->handle($this->requestFor($user), $next, 'admin', 'member');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('lolos', $response->getContent());
    }
}