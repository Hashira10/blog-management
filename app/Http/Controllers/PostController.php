<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Http\Requests\PostStoreRequest;
use App\Http\Requests\PostUpdateRequest;
use App\Http\Resources\PostResource;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Posts",
 *     description="API Endpoints for managing blog posts"
 * )
 */
class PostController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/posts",
     *     tags={"Posts"},
     *     summary="Get all posts with author, categories and tags",
     *     @OA\Response(
     *         response=200,
     *         description="List of posts with relationships"
     *     )
     * )
     */
    public function index()
    {
        $posts = Post::with(['categories', 'tags', 'author'])->get();
        return PostResource::collection($posts);
    }

    /**
     * @OA\Post(
     *     path="/api/posts",
     *     tags={"Posts"},
     *     summary="Create a new post",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "content", "status"},
     *             @OA\Property(property="title", type="string", example="My Post Title"),
     *             @OA\Property(property="content", type="string", example="This is the content."),
     *             @OA\Property(property="featured_image", type="string", nullable=true, example="images/post.jpg"),
     *             @OA\Property(property="status", type="string", example="published"),
     *             @OA\Property(property="categories", type="array", @OA\Items(type="integer"), example={1,2}),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="integer"), example={3,4})
     *         )
     *     ),
     *     @OA\Response(response=201, description="Post created successfully"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
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

        return new PostResource($post->load(['author', 'categories', 'tags']));
    }

    /**
     * @OA\Get(
     *     path="/api/posts/{id}",
     *     tags={"Posts"},
     *     summary="Get a specific post with its relations",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Post ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="Post data with relationships"),
     *     @OA\Response(response=404, description="Post not found")
     * )
     */
    public function show(Post $post)
    {
        return new PostResource($post->load(['author', 'categories', 'tags']));
    }

    /**
     * @OA\Put(
     *     path="/api/posts/{id}",
     *     tags={"Posts"},
     *     summary="Update a post",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Post ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Title"),
     *             @OA\Property(property="content", type="string", example="Updated content"),
     *             @OA\Property(property="featured_image", type="string", nullable=true, example="images/updated.jpg"),
     *             @OA\Property(property="status", type="string", example="draft"),
     *             @OA\Property(property="categories", type="array", @OA\Items(type="integer"), example={1}),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="integer"), example={2})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Post updated successfully"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Post not found")
     * )
     */
    public function update(PostUpdateRequest $request, Post $post)
    {
        $post->update($request->validated());

        if ($request->has('categories')) {
            $post->categories()->sync($request->categories);
        }

        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        return new PostResource($post->load(['author', 'categories', 'tags']));
    }

    /**
     * @OA\Delete(
     *     path="/api/posts/{id}",
     *     tags={"Posts"},
     *     summary="Delete a post",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Post ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=204, description="Post deleted successfully"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Post not found")
     * )
     */
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return response()->json(null, 204);
    }
}
