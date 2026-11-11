<?php

namespace App\Features\Admin\Controllers;

use App\Features\Admin\Services\AdminUserService;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController
{
    public function __construct(
        private readonly AdminUserService $service
    ) {}

    public function index(): Response
    {
        $filters = request()->only(['search']);
        $users   = $this->service->getAllUsers($filters, 15);

        return Inertia::render('Admin/UserList', [
            'users'   => $users,
            'filters' => $filters,
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,member'],
        ]);

        $this->service->updateRole($user, $validated['role']);

        return redirect()->route('admin.users.index')
            ->with('success', 'Role pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $deleted = $this->service->deleteUser($user, auth()->user());

        if (!$deleted) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}
