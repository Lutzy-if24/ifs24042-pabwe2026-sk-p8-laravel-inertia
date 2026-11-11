<?php

namespace App\Features\Post\Controllers;

use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Services\PostExcelService;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class AdminPostController
{
    public function __construct(
        private readonly PostRepositoryInterface $repository,
        private readonly PostExcelService        $excelService,
    ) {}

    public function index(): Response
    {
        $filters = request()->only(['search', 'category']);
        $posts   = $this->repository->allForAdmin($filters, 15);

        return Inertia::render('Admin/PostList', [
            'posts'      => $posts,
            'filters'    => $filters,
            'categories' => PostCategory::options(),
        ]);
    }

    public function exportAll(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return $this->excelService->exportAll();
    }

    public function userPosts(User $user): Response
    {
        $filters = request()->only(['search']);
        $posts   = $this->repository->allForUserAdmin($user->id, $filters, 15);

        return Inertia::render('Admin/UserPostList', [
            'user'    => $user,
            'posts'   => $posts,
            'filters' => $filters,
        ]);
    }

    public function exportUserPosts(User $user): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return $this->excelService->exportForUserAdmin($user->id);
    }
}
