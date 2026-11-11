<?php

namespace App\Features\Profile\Controllers;

use App\Features\Profile\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController
{
    public function __construct(
        private readonly ProfileService $service
    ) {}

    public function edit(): Response
    {
        return Inertia::render('Profile/Edit', [
            'user' => auth()->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'username'              => ['required', 'string', 'max:50', 'unique:users,username,' . $user->id, 'alpha_dash'],
            'email'                 => ['required', 'email', 'unique:users,email,' . $user->id],
            'password'              => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        $this->service->updateProfile($user, $validated);

        return redirect()->route('profile.edit')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:1024'],
        ]);

        $this->service->updatePhoto(auth()->user(), $request->file('photo'));

        return redirect()->route('profile.edit')
            ->with('success', 'Foto profil berhasil diperbarui.');
    }
}
