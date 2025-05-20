<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\PostController;

// Группа с аутентификацией и проверкой permission
Route::middleware(['auth:sanctum'])->group(function () {

    // Пример: только пользователи с правом 'manage_posts' могут создавать/редактировать/удалять посты
    Route::apiResource('posts', PostController::class)->middleware('permission:manage_posts');

    // Категории — например, проверка 'manage_categories'
    Route::apiResource('categories', CategoryController::class)->middleware('permission:manage_categories');

    // Теги — например, без особых прав, или можно добавить middleware
    Route::apiResource('tags', TagController::class);

    Route::post('/upload-image', [ImageUploadController::class, 'uploadFeaturedImage'])->middleware('permission:manage_posts');

    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password/forgot', [AuthController::class, 'forgotPassword']);
Route::post('/password/reset', [AuthController::class, 'resetPassword']);

// Можно оставить публичный доступ к списку постов без прав
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/tags', [TagController::class, 'index']);