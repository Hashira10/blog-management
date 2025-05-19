<?php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Http\Requests\PostStoreRequest;
use App\Http\Requests\PostUpdateRequest;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with(['categories', 'tags', 'author'])->get();
        return response()->json($posts, 200);
    }

    public function store(PostStoreRequest $request)
    {
        $post = Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'featured_image' => $request->featured_image ?? null,
            'status' => $request->status,
            'author_id' => auth()->id(),
        ]);

        if ($request->filled('categories')) {
            $post->categories()->sync($request->categories);
        }

        if ($request->filled('tags')) {
            $post->tags()->sync($request->tags);
        }

        return response()->json($post->load(['author', 'categories', 'tags']), 201);
    }

    public function show(Post $post)
    {
        return response()->json($post->load(['author', 'categories', 'tags']), 200);
    }

    public function update(PostUpdateRequest $request, Post $post)
    {
        // Авторизация теперь выполняется в PostUpdateRequest::authorize()
        $post->update($request->validated());

        if ($request->has('categories')) {
            $post->categories()->sync($request->categories);
        }

        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        return response()->json($post->load(['author', 'categories', 'tags']), 200);
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return response()->json(null, 204);
    }
}
