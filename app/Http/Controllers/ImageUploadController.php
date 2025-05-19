<?php

namespace App\Http\Controllers;

use App\Traits\UploadsImages;
use Illuminate\Http\Request;

class ImageUploadController extends Controller
{
    use UploadsImages;

    public function uploadFeaturedImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
        ]);

        $path = $this->uploadImage($request->file('image'), 'featured_images');

        return response()->json(['url' => $path]);
    }
}

