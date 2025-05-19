<?php
namespace App\Http\Controllers;

use App\Models\Tag;
use App\Http\Requests\TagStoreRequest;
use App\Http\Requests\TagUpdateRequest;

class TagController extends Controller
{
    public function index()
    {
        return Tag::all();
    }

    public function store(TagStoreRequest $request)
    {
        $tag = Tag::create($request->validated());

        return response()->json($tag, 201);
    }

    public function show(Tag $tag)
    {
        return $tag;
    }

    public function update(TagUpdateRequest $request, Tag $tag)
    {
        $tag->update($request->validated());

        return response()->json($tag, 200);
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();

        return response()->json(null, 204);
    }
}
