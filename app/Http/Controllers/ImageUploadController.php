<?php

namespace App\Http\Controllers;

use App\Traits\UploadsImages;
use App\Http\Requests\ImageUploadRequest;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Images",
 *     description="Image upload endpoints"
 * )
 */
class ImageUploadController extends Controller
{
    use UploadsImages;

    /**
     * @OA\Post(
     *     path="/api/images/featured",
     *     tags={"Images"},
     *     summary="Upload a featured image",
     *     security={{"sanctum":{}}},  
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"image"},
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="The image file to upload"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Image uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="url", type="string", example="/storage/featured_images/example.jpg")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function uploadFeaturedImage(ImageUploadRequest $request)
    {
        $path = $this->uploadImage($request->file('image'), 'featured_images');

        return response()->json(['url' => $path]);
    }
}
