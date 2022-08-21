<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Tag;

class TagController extends Controller
{

    public function index()
    {
        $tags = Tag::where('user_id', Auth::user()->id)->orderBy('name')->get();
        return response()->json(['data' => $tags]);
    }

    public function show($id)
    {
        if (!is_numeric($id)) {
            return response()->json('Request requires an id', 400);
        }
        $tag = Tag::find($id);
        if (!$tag) {
            return response()->json('Tag not found', 404);
        }
        $this->authorize('view', $tag);
        return response()->json(['data' => $tag]);
    }

    public function store(Request $request)
    {

        $this->validate($request, [
            'name' => 'required_without:tags|string',
            'tags.*.name' => 'required_without:name|string',
        ]);

        $user_id = Auth::user()->id;
        $tags = [];

        if (isset($request->name)) {
            $tag = $this->saveTag($request->name, $user_id);
            if ($tag) {
                return response()->json(['data' => $tag], 201);
            }
            return response()->json('Tag does already exist', 400);
        } else if (isset($request->tags)) {
            foreach ($request->tags as $tag_request_object) {
                $tag = $this->saveTag($tag_request_object['name'], $user_id);
                if ($tag) {
                    $tags[] = $tag;
                }
            }
        }

        if (count($tags) === 0) {
            return response()->json('', 200);
        }

        return response()->json(['data' => $tags], 201);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string'
        ]);

        $tag = Tag::find($id);
        $this->authorize('update', $tag);

        $tag->name = $request->name;
        $tag->save();

        return response()->json(['data' => $tag], 200);
    }

    public function destroy($id)
    {
        $tag = Tag::find($id);
        if (!$tag) {
            return response()->json('Tag not found.', 400);
        }
        $this->authorize('delete', $tag);
        $tag->delete();

        return response()->json('', 204);
    }

    private function saveTag(string $name, int $user_id)
    {
        if (Tag::where('name', $name)
            ->where('user_id', $user_id)->exists()
        ) {
            return null;
        }

        $tag = Tag::create([
            'name' => $name,
            'user_id' => $user_id
        ]);

        return $tag;
    }
}
