<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder; // <-- Добавьте это
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Вызов фабрики пользователей
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
