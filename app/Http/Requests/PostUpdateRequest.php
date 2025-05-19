<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostUpdateRequest extends FormRequest
{
    public function authorize()
    {
        // Разрешаем обновлять пост только его автору или админу
        $post = $this->route('post'); // Получаем модель Post из маршрута

        return $post && (auth()->id() === $post->author_id || auth()->user()->is_admin);
    }

    public function rules()
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'featured_image' => 'nullable|string',
            'status' => 'sometimes|required|in:draft,published',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
        ];
    }
}
