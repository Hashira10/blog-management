<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TagStoreRequest extends FormRequest
{
    public function authorize()
    {
        // Вернуть true, если авторизация не нужна, иначе логика проверки прав
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|unique:tags,name',
        ];
    }
}
