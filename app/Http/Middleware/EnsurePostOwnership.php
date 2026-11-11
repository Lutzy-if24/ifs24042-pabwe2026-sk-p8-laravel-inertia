<?php

namespace App\Http\Middleware;

use App\Features\Post\Models\Post;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePostOwnership
{
    public function handle(Request $request, Closure $next): Response
    {
        $post = $request->route('post');

        if ($post instanceof Post && $post->user_id !== $request->user()?->id) {
            abort(403, 'Anda tidak memiliki akses ke artikel ini.');
        }

        return $next($request);
    }
}
