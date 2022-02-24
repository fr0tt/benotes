<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Post;
use App\Collection;
use App\User;
use App\Services\PostService;
use Symfony\Component\HttpFoundation\Response;

class PostController extends Controller
{

    private $service;

    public function __construct()
    {
        $this->service = new PostService();
    }

    public function index(Request $request)
    {

        $this->validate($request, [
            'collection_id'    => 'integer|nullable',
            'is_uncategorized' => 'nullable',
            // should support: 0, 1, true, false, "true", "false" because
            // it should/could be used in a query string
            'filter'           => 'string|nullable',
            'is_archived'      => 'nullable', // same as is_uncategorized
            'limit'            => 'integer|nullable',
        ]);

        $auth_type = User::getAuthenticationType();

        if ($auth_type === User::UNAUTHORIZED_USER) {
            return response()->json('', Response::HTTP_UNAUTHORIZED);
        }

        $posts = $this->service->all(
            intval($request->collection_id),
            $this->service->boolValue($request->is_uncategorized),
            strval($request->filter),
            $auth_type,
            $this->service->boolValue($request->is_archived),
            intval($request->limit)
        );

        return response()->json(['data' => $posts], Response::HTTP_OK);
    }

    public function show(int $id)
    {
        $post = Post::find($id);
        if ($post === null) {
            return response()->json('Post does not exist', Response::HTTP_NOT_FOUND);
        }
        $this->authorize('view', $post);
        return response()->json(['data' => $post], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, [
            'title' => 'string|nullable',
            'content' => 'required|string',
            'collection_id' => 'integer|nullable'
        ]);

        if (isset($request->collection_id)) {
            $collection = Collection::findOrFail($request->collection_id);
            if (Auth::user()->id !== $collection->user_id) {
                return response()->json('Not authorized', Response::HTTP_FORBIDDEN);
            }
        }

        $validatedData['content'] = $this->service->sanitize($validatedData['content']);

        $info = $this->service->computePostData($request->title, $request->content);

        $attributes = array_merge($validatedData, $info);
        $attributes['user_id'] = Auth::user()->id;
        $attributes['order'] = Post::where('collection_id', Collection::getCollectionId($request->collection_id))
            ->max('order') + 1;

        $post = Post::create($attributes);
        if ($info['type'] === Post::POST_TYPE_LINK) {
            $this->service->saveImage($info['image_path'], $post);
        }

        return response()->json(['data' => $post], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {

        $validatedData = $this->validate($request, [
            'title' => 'string|nullable',
            'content' => 'string|nullable',
            'collection_id' => 'integer|nullable',
            'is_uncategorized' => 'boolean|nullable',
            'order' => 'integer|nullable',
            'is_archived' => 'boolean|nullable'
        ]);

        $post = Post::withTrashed()->find($id);
        if (!$post) {
            return response()->json('Post not found.', Response::HTTP_NOT_FOUND);
        }

        $this->authorize('update', $post);

        $request->is_uncategorized = boolval($request->is_uncategorized);

        if (empty($request->collection_id) && $request->is_uncategorized === false) {
            // request contains no knowledge about a collection
            $validatedData['collection_id'] = $post->collection_id;
        } else {
            $validatedData['collection_id'] = Collection::getCollectionId(
                $request->collection_id,
                $request->is_uncategorized
            );
        }

        if (!empty($validatedData['collection_id'])) {
            Collection::findOrFail($validatedData['collection_id']);
        }

        if (isset($validatedData['content'])) {
            $validatedData['content'] = $this->service->sanitize($validatedData['content']);
            $info = $this->service->computePostData($request->title, $validatedData['content']);
        } else {
            $info = array();
            $info['type'] = $post->getRawOriginal('type');
        }

        $newValues = array_merge($validatedData, $info);
        $newValues['user_id'] = Auth::user()->id;

        if ($post->collection_id !== $validatedData['collection_id']) {
            // post wants to have a different collection than before
            // compute order in new collection
            $newValues['order'] = Post::where('collection_id', $validatedData['collection_id'])
                ->max('order') + 1;
            // reorder old collection
            Post::where('collection_id', $post->collection_id)
                ->where('order', '>', $post->order)->decrement('order');
        } else if (isset($validatedData['order'])) {
            // post wants to only be positioned somewhere else
            // staying in the same collection as before
            $newOrder = $validatedData['order'];
            $oldOrder = $post->order;
            if ($newOrder !== $oldOrder) {
                if ($newOrder > $oldOrder) {
                    Post::where('collection_id', $post->collection_id)
                        ->whereBetween('order', [$oldOrder + 1, $newOrder])->decrement('order');
                } else {
                    Post::where('collection_id', $post->collection_id)
                        ->whereBetween('order', [$newOrder, $oldOrder - 1])->increment('order');
                }
            }
        }

        $post->update($newValues);

        if (isset($request->is_archived)) {
            $request->is_archived = filter_var($request->is_archived, FILTER_VALIDATE_BOOLEAN);
            $is_currently_archived = $post->trashed();
            if ($request->is_archived === true && $is_currently_archived === false) {
                $post = $this->service->delete($post);
            } else if ($request->is_archived === false && $is_currently_archived === true) {
                $post = $this->service->restore($post);
            }
        }

        if ($info['type'] === Post::POST_TYPE_LINK && isset($validatedData['content'])) {
            $this->service->saveImage($info['image_path'], $post);
        }

        return response()->json(['data' => $post], Response::HTTP_OK);
    }

    public function destroy(int $id)
    {

        $post = Post::find($id);

        if (!$post) {
            return response()->json('Post not found.', Response::HTTP_NOT_FOUND);
        }
        $this->authorize('delete', $post);

        $this->service->delete($post);

        return response()->json('', Response::HTTP_NO_CONTENT);

    }

    public function getUrlInfo(Request $request)
    {
        $this->validate($request, [
            'url' => 'url'
        ]);

        return response()->json($this->service->getInfo($request->url));
    }

}