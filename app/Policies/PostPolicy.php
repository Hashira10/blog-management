<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function update(User $user, Post $post)
    {
        return $user->id === $post->author_id || $user->is_admin;
    }

    public function delete(User $user, Post $post)
    {
        return $user->id === $post->author_id || $user->is_admin;
    }
}
