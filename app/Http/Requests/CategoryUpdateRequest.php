<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true; // или проверка прав пользователя
    }

    public function rules()
    {
        // Получаем ID категории из маршрута
        $categoryId = $this->route('category')->id;

        return [
            'name' => [
                'required',
                'string',
                Rule::unique('categories', 'name')->ignore($categoryId),
            ],
        ];
    }
}
