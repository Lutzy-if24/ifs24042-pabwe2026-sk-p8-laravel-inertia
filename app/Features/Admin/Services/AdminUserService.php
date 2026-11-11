<?php

namespace App\Features\Admin\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminUserService
{
    public function getAllUsers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::orderByDesc('created_at');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('username', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function findUserById(int $id): ?User
    {
        return User::find($id);
    }

    public function updateRole(User $user, string $role): User
    {
        $user->update(['role' => $role]);
        return $user->fresh();
    }

    public function deleteUser(User $user, User $currentAdmin): bool
    {
        if ($user->id === $currentAdmin->id) {
            return false; // Admin tidak boleh hapus dirinya sendiri
        }
        return (bool) $user->delete();
    }
}
