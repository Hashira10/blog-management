<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $headers = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Создаем пользователя и аутентифицируемся через Sanctum
        $this->user = User::factory()->create();

        $this->actingAs($this->user, 'sanctum');
    }

    public function test_user_can_create_post()
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $data = [
            'title' => 'Test Post',
            'content' => 'This is a test post content.',
            'status' => 'draft',
            'categories' => [$category->id],
            'tags' => [$tag->id],
        ];

        $response = $this->postJson(route('posts.store'), $data);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'title' => 'Test Post',
                'content' => 'This is a test post content.',
                'status' => 'draft',
                'author_id' => $this->user->id,
            ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'author_id' => $this->user->id,
        ]);

        $postId = $response->json('id');
        $post = Post::find($postId);

        $this->assertTrue($post->categories->contains($category));
        $this->assertTrue($post->tags->contains($tag));
    }

    public function test_user_can_view_post()
    {
        $post = Post::factory()->for($this->user, 'author')->create();

        $response = $this->getJson(route('posts.show', $post));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $post->id,
                'title' => $post->title,
            ]);
    }

    public function test_user_can_update_own_post()
    {
        $post = Post::factory()->for($this->user, 'author')->create([
            'title' => 'Old Title',
            'content' => 'Old content',
        ]);

        $data = [
            'title' => 'New Title',
            'content' => 'New content',
            'status' => 'published',
        ];

        $response = $this->putJson(route('posts.update', $post), $data);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'title' => 'New Title',
                'content' => 'New content',
                'status' => 'published',
            ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'New Title',
        ]);
    }

    public function test_user_cannot_update_others_post()
    {
        $otherUser = User::factory()->create();
        $post = Post::factory()->for($otherUser, 'author')->create();

        $data = [
            'title' => 'New Title',
        ];

        $response = $this->putJson(route('posts.update', $post), $data);

        $response->assertStatus(403); // Запрещено
    }

    public function test_user_can_delete_own_post()
    {
        $post = Post::factory()->for($this->user, 'author')->create();

        $response = $this->deleteJson(route('posts.destroy', $post));

        $response->assertStatus(204);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_user_cannot_delete_others_post()
    {
        $otherUser = User::factory()->create();
        $post = Post::factory()->for($otherUser, 'author')->create();

        $response = $this->deleteJson(route('posts.destroy', $post));

        $response->assertStatus(403);
    }
}
