<?php
namespace App\Http\Controllers;

use App\Traits\UploadsImages;
use App\Http\Requests\ImageUploadRequest;

class ImageUploadController extends Controller
{
    use UploadsImages;

    public function uploadFeaturedImage(ImageUploadRequest $request)
    {
        $path = $this->uploadImage($request->file('image'), 'featured_images');

        return response()->json(['url' => $path]);
    }
}
