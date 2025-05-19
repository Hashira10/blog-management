<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Отключаем проверки внешних ключей
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Очищаем связанные таблицы
        DB::table('role_permission')->truncate();
        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();

        // Включаем проверки обратно
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Создаём разрешения
        $permissions = [
            'manage_posts',
            'publish_posts',
            'edit_posts',
            'delete_posts',
            'manage_categories',
            'manage_users'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Создаём роли и привязываем разрешения
        $roles = [
            'Admin' => ['manage_posts', 'publish_posts', 'edit_posts', 'delete_posts', 'manage_categories', 'manage_users'],
            'Editor' => ['manage_posts', 'publish_posts', 'edit_posts'],
            'Author' => ['edit_posts', 'publish_posts'],
            'Reader' => []
        ];

        foreach ($roles as $roleName => $perms) {
            $role = Role::create(['name' => $roleName]);
            $role->permissions()->sync(
                Permission::whereIn('name', $perms)->pluck('id')->toArray()
            );
        }
    }
}
