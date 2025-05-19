<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TagUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        // Получаем модель Tag из маршрута
        $tag = $this->route('tag');

        $tagId = $tag ? $tag->id : null;

        return [
            'name' => [
                'required',
                'string',
                Rule::unique('tags', 'name')->ignore($tagId),
            ],
        ];
    }
}
