<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id'                 => $request->user()->id,
                    'name'               => $request->user()->name,
                    'username'           => $request->user()->username,
                    'email'              => $request->user()->email,
                    'role'               => $request->user()->role,
                    'profile_photo_path' => $request->user()->profile_photo_path,
                    'profile_photo_url'  => $request->user()->profile_photo_path
                        ? asset('storage/' . $request->user()->profile_photo_path)
                        : null,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}
