<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password123'),
            ]
        );

        $permissionPosts = Permission::firstOrCreate(['name' => 'manage_posts']);
        $permissionCategories = Permission::firstOrCreate(['name' => 'manage_categories']);

        $role = Role::firstOrCreate(['name' => 'Admin']);
        $role->permissions()->sync([$permissionPosts->id, $permissionCategories->id]);

        $user->roles()->sync([$role->id]);
    }
}
