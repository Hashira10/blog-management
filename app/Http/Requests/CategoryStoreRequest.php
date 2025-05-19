<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true; // или проверка прав пользователя
    }

    public function rules()
    {
        return [
            'name' => 'required|string|unique:categories,name',
        ];
    }
}
