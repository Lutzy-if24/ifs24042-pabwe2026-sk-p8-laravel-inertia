<?php

use App\Features\Admin\Controllers\AdminUserController;
use App\Features\Auth\Controllers\AuthController;
use App\Features\Post\Controllers\AdminPostController;
use App\Features\Post\Controllers\PostController;
use App\Features\Post\Controllers\PublicPostController;
use App\Features\Profile\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (Guest / All)
|--------------------------------------------------------------------------
*/

Route::get('/', [PublicPostController::class, 'index'])->name('public.index');
Route::get('/articles', [PublicPostController::class, 'index'])->name('public.articles');
Route::get('/articles/{slug}', [PublicPostController::class, 'show'])->name('public.articles.show');

/*
|--------------------------------------------------------------------------
| Auth Routes (Guest only)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Profile Routes (Authenticated)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');
});

/*
|--------------------------------------------------------------------------
| Dashboard / Member Routes
| IMPORTANT: static routes BEFORE {post} route
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:member,admin'])->prefix('dashboard')->name('dashboard.')->group(function () {

    // Static routes MUST be before /{post} to avoid route-model-binding conflict
    Route::get('/posts/export', [PostController::class, 'export'])->name('posts.export');
    Route::post('/posts/import', [PostController::class, 'import'])->name('posts.import');
    Route::post('/posts/ai-generate', [PostController::class, 'aiGenerate'])->name('posts.ai-generate');
    Route::post('/posts/ai-save', [PostController::class, 'aiSave'])->name('posts.ai-save');

    // Resource-like routes
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');

    // Routes with {post} model binding
    Route::middleware('post.owner')->group(function () {
        Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
        Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
        Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
        Route::patch('/posts/{post}/publish', [PostController::class, 'publish'])->name('posts.publish');
        Route::post('/posts/{post}/cover', [PostController::class, 'uploadCover'])->name('posts.cover.upload');
        Route::delete('/posts/{post}/cover', [PostController::class, 'deleteCover'])->name('posts.cover.delete');
    });
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // User management
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    // User posts (read-only)
    Route::get('/users/{user}/posts', [AdminPostController::class, 'userPosts'])->name('users.posts');
    Route::get('/users/{user}/posts/export', [AdminPostController::class, 'exportUserPosts'])->name('users.posts.export');

    // All posts (read-only)
    Route::get('/posts', [AdminPostController::class, 'index'])->name('posts.index');
    Route::get('/posts/export', [AdminPostController::class, 'exportAll'])->name('posts.export');
});
