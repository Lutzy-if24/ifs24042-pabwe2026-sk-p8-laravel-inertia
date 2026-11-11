<?php

namespace App\Features\Post\Controllers;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Features\Post\Services\PostExcelService;
use App\Features\Post\Services\PostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PostController
{
    public function __construct(
        private readonly PostRepositoryInterface $repository,
        private readonly PostService             $postService,
        private readonly PostExcelService        $excelService,
        private readonly AiClientInterface       $aiClient,
    ) {}

    public function index(): Response
    {
        $userId  = auth()->id();
        $filters = request()->only(['search', 'is_published']);
        $posts   = $this->repository->allForUser($userId, $filters, 15);
        $stats   = $this->postService->statsByUser($userId);

        return Inertia::render('Post/Index', [
            'posts'      => $posts,
            'filters'    => $filters,
            'stats'      => $stats,
            'categories' => PostCategory::options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'category'     => ['required', 'string', 'in:' . implode(',', PostCategory::values())],
            'excerpt'      => ['nullable', 'string', 'max:500'],
            'content'      => ['required', 'string'],
            'is_published' => ['boolean'],
        ]);

        $this->postService->createPost(auth()->id(), $validated);

        return redirect()->route('dashboard.posts.index')
            ->with('success', 'Artikel berhasil disimpan.');
    }

    public function show(Post $post): Response
    {
        $this->authorize('view', $post);
        return Inertia::render('Post/Show', ['post' => $post->load('user')]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $this->authorize('update', $post);

        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'category'     => ['required', 'string', 'in:' . implode(',', PostCategory::values())],
            'excerpt'      => ['nullable', 'string', 'max:500'],
            'content'      => ['required', 'string'],
            'is_published' => ['boolean'],
        ]);

        $this->postService->updatePost($post, $validated);

        return redirect()->route('dashboard.posts.index')
            ->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);
        $this->postService->deletePost($post);

        return redirect()->route('dashboard.posts.index')
            ->with('success', 'Artikel berhasil dihapus.');
    }

    public function publish(Post $post): RedirectResponse
    {
        $this->authorize('update', $post);
        $this->postService->togglePublish($post);

        return redirect()->route('dashboard.posts.index')
            ->with('success', 'Status artikel berhasil diperbarui.');
    }

    public function uploadCover(Request $request, Post $post): RedirectResponse
    {
        $this->authorize('update', $post);

        $request->validate([
            'cover' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $this->postService->uploadCover($post, $request->file('cover'));

        return redirect()->route('dashboard.posts.index')
            ->with('success', 'Cover berhasil diunggah.');
    }

    public function deleteCover(Post $post): RedirectResponse
    {
        $this->authorize('update', $post);
        $this->postService->deleteCover($post);

        return redirect()->route('dashboard.posts.index')
            ->with('success', 'Cover berhasil dihapus.');
    }

    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return $this->excelService->exportForUser(auth()->id());
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ]);

        $path   = $request->file('file')->store('imports', 'local');
        $result = $this->excelService->importFromFile(storage_path("app/private/{$path}"), auth()->id());

        $message = "Berhasil mengimpor {$result['imported']} artikel.";
        if (!empty($result['errors'])) {
            $message .= ' Beberapa baris dilewati: ' . implode('; ', $result['errors']);
        }

        return redirect()->route('dashboard.posts.index')->with('success', $message);
    }

    public function aiGenerate(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'count' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        try {
            $posts = $this->aiClient->generatePosts($validated['count']);
            return response()->json([
                'success' => true,
                'posts'   => array_map(fn ($p) => $p->toArray(), $posts),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function aiSave(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'posts'                => ['required', 'array', 'min:1'],
            'posts.*.title'        => ['required', 'string', 'max:255'],
            'posts.*.category'     => ['required', 'string', 'in:' . implode(',', PostCategory::values())],
            'posts.*.excerpt'      => ['nullable', 'string'],
            'posts.*.content'      => ['required', 'string'],
        ]);

        foreach ($validated['posts'] as $postData) {
            $this->postService->createPost(auth()->id(), array_merge($postData, ['is_published' => false]));
        }

        return redirect()->route('dashboard.posts.index')
            ->with('success', count($validated['posts']) . ' artikel AI berhasil disimpan sebagai draf.');
    }

    private function authorize(string $ability, Post $post): void
    {
        if (!auth()->user()->can($ability, $post)) {
            abort(403);
        }
    }
}
