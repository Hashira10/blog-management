<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    public function uploadFeaturedImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048', // max 2MB, проверка что это картинка
        ]);

        $path = $request->file('image')->store('featured_images', 'public');

        return response()->json([
            'url' => Storage::url($path)
        ]);
    }
}
