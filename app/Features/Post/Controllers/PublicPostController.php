<?php

namespace App\Features\Post\Controllers;

use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Enums\PostCategory;
use Inertia\Inertia;
use Inertia\Response;

class PublicPostController
{
    public function __construct(
        private readonly PostRepositoryInterface $repository
    ) {}

    public function index(): Response
    {
        $filters = request()->only(['search', 'category']);
        $posts   = $this->repository->allPublished($filters, 12);

        return Inertia::render('Public/PostIndex', [
            'posts'      => $posts,
            'filters'    => $filters,
            'categories' => PostCategory::options(),
        ]);
    }

    public function show(string $slug): Response|\Illuminate\Http\Response
    {
        $post = $this->repository->findPublishedBySlug($slug);

        if (!$post) {
            abort(404);
        }

        return Inertia::render('Public/PostShow', [
            'post' => $post->load('user'),
        ]);
    }
}
