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

        // Создаем разрешения
        $permissionPosts = Permission::firstOrCreate(['name' => 'manage_posts']);
        $permissionCategories = Permission::firstOrCreate(['name' => 'manage_categories']);

        // Создаем роль Admin и привязываем разрешения
        $role = Role::firstOrCreate(['name' => 'Admin']);
        $role->permissions()->sync([$permissionPosts->id, $permissionCategories->id]);

        // Создаем пользователя
        $this->user = User::factory()->create();

        // Назначаем роль пользователю
        $this->user->roles()->sync([$role->id]);

        // Обновляем пользователя из базы, чтобы загрузить роли и разрешения
        $this->user = $this->user->fresh();

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
            'categories' => [$category->id], // Передаем ID категории
            'tags' => [$tag->id],           // Передаем ID тега
        ];

        $response = $this->postJson(route('posts.store'), $data);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'title' => 'Test Post',
                'content' => 'This is a test post content.',
                'status' => 'draft',
            ])
            ->assertJsonFragment([
                'id' => $this->user->id,      // ID автора
                'name' => $this->user->name,  // Имя автора
                'email' => $this->user->email, // Email автора
            ])
            ->assertJsonFragment([
                'categories' => [$category->id], // Проверка ID категории
                'tags' => [$tag->id],           // Проверка ID тега
            ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'author_id' => $this->user->id,
        ]);

        $postId = $response->json('data.id');
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

    public function test_user_can_create_category()
    {
        $data = ['name' => 'New Category'];

        $response = $this->postJson(route('categories.store'), $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'New Category']);

        $this->assertDatabaseHas('categories', ['name' => 'New Category']);
    }

    public function test_user_can_view_category()
    {
        $category = Category::factory()->create();

        $response = $this->getJson(route('categories.show', $category));

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $category->id, 'name' => $category->name]);
    }

    public function test_user_can_update_category()
    {
        $category = Category::factory()->create(['name' => 'Old Name']);

        $data = ['name' => 'Updated Name'];

        $response = $this->putJson(route('categories.update', $category), $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Name']);

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Updated Name']);
    }

    public function test_user_can_delete_category()
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson(route('categories.destroy', $category));

        $response->assertStatus(204);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    // --- Тесты для тегов ---

    public function test_user_can_create_tag()
    {
        $data = ['name' => 'New Tag'];

        $response = $this->postJson(route('tags.store'), $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'New Tag']);

        $this->assertDatabaseHas('tags', ['name' => 'New Tag']);
    }

    public function test_user_can_view_tag()
    {
        $tag = Tag::factory()->create();

        $response = $this->getJson(route('tags.show', $tag));

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $tag->id, 'name' => $tag->name]);
    }

    public function test_user_can_update_tag()
    {
        $tag = Tag::factory()->create(['name' => 'Old Tag']);

        $data = ['name' => 'Updated Tag'];

        $response = $this->putJson(route('tags.update', $tag), $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Tag']);

        $this->assertDatabaseHas('tags', ['id' => $tag->id, 'name' => 'Updated Tag']);
    }

    public function test_user_can_delete_tag()
    {
        $tag = Tag::factory()->create();

        $response = $this->deleteJson(route('tags.destroy', $tag));

        $response->assertStatus(204);

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }


}
