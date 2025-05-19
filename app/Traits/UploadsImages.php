<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait UploadsImages
{
    public function uploadImage($file, $directory = 'uploads', $disk = 'public')
    {
        return $file->store($directory, $disk);
    }

    public function deleteImage($path, $disk = 'public')
    {
        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }
}
