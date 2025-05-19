<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionFullTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_roles_and_permissions_and_assign_them_correctly()
    {
        // Создаем роли
        $roles = [
            'Admin',
            'Editor',
            'Author',
            'Reader',
        ];
        foreach ($roles as $roleName) {
            Role::create(['name' => $roleName]);
        }

        // Создаем права
        $permissions = [
            'manage_posts',
            'publish_posts',
            'edit_posts',
            'delete_posts',
            'manage_categories',
            'manage_users',
        ];
        foreach ($permissions as $permName) {
            Permission::create(['name' => $permName]);
        }

        // Проверяем, что роли создались
        foreach ($roles as $roleName) {
            $this->assertDatabaseHas('roles', ['name' => $roleName]);
        }

        // Проверяем, что права создались
        foreach ($permissions as $permName) {
            $this->assertDatabaseHas('permissions', ['name' => $permName]);
        }

        // Пример: Привязываем права к ролям
        $admin = Role::where('name', 'Admin')->first();
        $editor = Role::where('name', 'Editor')->first();
        $author = Role::where('name', 'Author')->first();
        $reader = Role::where('name', 'Reader')->first();

        // Admin — все права
        $admin->permissions()->sync(Permission::all()->pluck('id'));

        // Editor — publish, edit, delete posts
        $editorPermissions = Permission::whereIn('name', ['publish_posts', 'edit_posts', 'delete_posts'])->pluck('id');
        $editor->permissions()->sync($editorPermissions);

        // Author — manage_posts, edit_posts
        $authorPermissions = Permission::whereIn('name', ['manage_posts', 'edit_posts'])->pluck('id');
        $author->permissions()->sync($authorPermissions);

        // Reader — нет прав
        $reader->permissions()->sync([]);

        // Проверяем связи ролей и прав
        $this->assertEquals(Permission::count(), $admin->permissions()->count());
        $this->assertEquals(3, $editor->permissions()->count());
        $this->assertEquals(2, $author->permissions()->count());
        $this->assertEquals(0, $reader->permissions()->count());

        // Создаем пользователя и назначаем ему роль Editor
        $user = User::factory()->create();
        $user->roles()->attach($editor);

        $this->assertTrue($user->roles->contains($editor));

        // Проверка метода hasPermission — должен быть реализован в User
        $this->assertTrue($user->hasPermission('publish_posts'));
        $this->assertFalse($user->hasPermission('manage_users'));
    }
}
