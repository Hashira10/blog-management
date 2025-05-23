<?php
// database/factories/PostFactory.php
namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
            'status' => 'draft',
            'author_id' => User::factory(), // правильное поле
            'featured_image' => null,
            // другие поля если есть
        ];
    }
}
