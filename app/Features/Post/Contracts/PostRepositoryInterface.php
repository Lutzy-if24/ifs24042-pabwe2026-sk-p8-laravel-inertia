<?php

namespace App\Features\Post\Contracts;

use App\Features\Post\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PostRepositoryInterface
{
    public function allPublished(array $filters = [], int $perPage = 12): LengthAwarePaginator;

    public function findPublishedBySlug(string $slug): ?Post;

    public function allForUser(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function allForAdmin(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function allForUserAdmin(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Post;

    public function findBySlug(string $slug): ?Post;

    public function create(array $data): Post;

    public function update(Post $post, array $data): Post;

    public function delete(Post $post): bool;

    public function getForExport(int $userId): Collection;

    public function getAllForExport(): Collection;

    public function getAllForUserExport(int $userId): Collection;

    public function statsByUser(int $userId): array;
}
