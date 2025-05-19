<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;


class PostCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаем разрешение manage_posts
        $permission = Permission::firstOrCreate(['name' => 'manage_posts']);

        // Создаем роль Admin и привязываем разрешение
        $role = Role::firstOrCreate(['name' => 'Admin']);
        $role->permissions()->syncWithoutDetaching([$permission->id]);

        // Создаем пользователя
        $this->user = User::factory()->create();

        // Назначаем пользователю роль Admin
        $this->user->roles()->syncWithoutDetaching([$role->id]);

        // Аутентифицируемся под этим пользователем
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

        $response->assertStatus(403);
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

    public function test_user_can_upload_featured_image()
    {
        // Создаем поддельное изображение
        $file = \Illuminate\Http\UploadedFile::fake()->image('test-image.jpg', 800, 600);

        // Отправляем запрос на загрузку изображения
        $response = $this->postJson('/api/upload-image', [
            'image' => $file,
        ]);

        // Проверяем, что запрос выполнен успешно
        $response->assertStatus(200)
            ->assertJsonStructure(['url']);

        // Проверяем, что изображение было сохранено в правильной директории
        Storage::disk('public')->assertExists("featured_images/{$file->hashName()}");

        // Удаляем тестовое изображение после теста
        Storage::disk('public')->delete("featured_images/{$file->hashName()}");
    }


}
