<?php
namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{

    public function index()
    {
        $posts = Post::with(['categories', 'tags', 'author'])->get();
        return response()->json($posts, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'featured_image' => 'nullable|string',
            'status' => 'required|in:draft,published',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
        ]);

        $post = Post::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'featured_image' => $validated['featured_image'] ?? null,
            'status' => $validated['status'],
            'author_id' => auth()->id(),
        ]);

        if (!empty($validated['categories'])) {
            $post->categories()->sync($validated['categories']);
        }

        if (!empty($validated['tags'])) {
            $post->tags()->sync($validated['tags']);
        }

        return response()->json($post->load(['author', 'categories', 'tags']), 201);
    }

    public function show(Post $post)
    {
        return response()->json($post->load(['author', 'categories', 'tags']), 200);
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'featured_image' => 'nullable|string',
            'status' => 'sometimes|required|in:draft,published',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
        ]);
        \Log::info('Auth user id:', [auth()->id()]);
        \Log::info('Post author id:', [$post->author_id]);
        \Log::info('User is admin:', [auth()->user()->is_admin ?? 'no user']);

        $post->update($validated);

        if (isset($validated['categories'])) {
            $post->categories()->sync($validated['categories']);
        }

        if (isset($validated['tags'])) {
            $post->tags()->sync($validated['tags']);
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
