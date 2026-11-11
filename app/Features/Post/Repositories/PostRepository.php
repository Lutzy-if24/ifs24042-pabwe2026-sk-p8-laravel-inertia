<?php

namespace App\Features\Post\Repositories;

use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PostRepository implements PostRepositoryInterface
{
    public function allPublished(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Post::with('user')
            ->where('is_published', true)
            ->orderByDesc('published_at');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereLike('title', "%{$search}%")
                  ->orWhereLike('content', "%{$search}%");
            });
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function findPublishedBySlug(string $slug): ?Post
    {
        return Post::with('user')
            ->where('slug', $slug)
            ->where('is_published', true)
            ->first();
    }

    public function allForUser(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Post::where('user_id', $userId)
            ->orderByDesc('created_at');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereLike('title', "%{$search}%")
                  ->orWhereLike('excerpt', "%{$search}%");
            });
        }

        if (isset($filters['is_published']) && $filters['is_published'] !== '') {
            $query->where('is_published', (bool) $filters['is_published']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function allForAdmin(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Post::with('user')->orderByDesc('created_at');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereLike('title', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->whereLike('name', "%{$search}%"));
            });
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function allForUserAdmin(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Post::with('user')
            ->where('user_id', $userId)
            ->orderByDesc('created_at');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereLike('title', "%{$search}%");
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function findById(int $id): ?Post
    {
        return Post::find($id);
    }

    public function findBySlug(string $slug): ?Post
    {
        return Post::where('slug', $slug)->first();
    }

    public function create(array $data): Post
    {
        return Post::create($data);
    }

    public function update(Post $post, array $data): Post
    {
        $post->update($data);
        return $post->fresh();
    }

    public function delete(Post $post): bool
    {
        return $post->delete();
    }

    public function getForExport(int $userId): Collection
    {
        return Post::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function getAllForExport(): Collection
    {
        return Post::with('user')
            ->orderByDesc('created_at')
            ->get();
    }

    public function getAllForUserExport(int $userId): Collection
    {
        return Post::with('user')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function statsByUser(int $userId): array
    {
        $total     = Post::where('user_id', $userId)->count();
        $published = Post::where('user_id', $userId)->where('is_published', true)->count();
        $draft     = Post::where('user_id', $userId)->where('is_published', false)->count();

        return compact('total', 'published', 'draft');
    }
}