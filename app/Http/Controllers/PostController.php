<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Post;
use App\Models\Collection;
use App\Models\User;
use App\Models\Tag;
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
            'tag_id'           => 'integer|nullable',
            'withTags'         => 'nullable',
            'filter'           => 'string|nullable',
            'is_archived'      => 'nullable',
            // same as is_uncategorized
            'after_id'         => 'integer|nullable',
            'offset'           => 'integer|nullable',
            'limit'            => 'integer|nullable',
        ]);

        $auth_type = User::getAuthenticationType();

        if ($auth_type === User::UNAUTHORIZED_USER) {
            return response()->json('', Response::HTTP_UNAUTHORIZED);
        }

        $after_post = null;
        if (isset($request->after_id)) {
            if (!isset($request->collection_id) && isset($request->is_uncategorized)) {
                return response()->json(
                    'collection_id or is_uncategorized is required',
                    Response::HTTP_BAD_REQUEST
                );
            }
            $after_post = Post::find($request->after_id);
            if ($after_post == null) {
                return response()->json('after_id does not exist', Response::HTTP_NOT_FOUND);
            }
            $this->authorize('view', $after_post);
            $collection_id = Collection::getCollectionId(
                $request->collection_id,
                $request->is_uncategorized
            );
            if ($after_post->collection_id !== $collection_id) {
                return response()->json('Wrong collection', Response::HTTP_BAD_REQUEST);
            }
        }

        if (isset($request->tag_id)) {
            if (!Tag::find($request->tag_id)->exists()) {
                return response()->json('Tag does not exist', Response::HTTP_BAD_REQUEST);
            }
        }

        $posts = $this->service->all(
            intval($request->collection_id),
            $this->service->boolValue($request->is_uncategorized),
            intval($request->tag_id),
            $this->service->boolValue($request->withTags),
            strval($request->filter),
            $auth_type,
            $this->service->boolValue($request->is_archived),
            $after_post,
            intval($request->offset),
            intval($request->limit),
        );

        return response()->json(['data' => $posts], Response::HTTP_OK);
    }

    public function show(int $id, Request $request)
    {

        $this->validate($request, [
            'withTags' => 'nullable',
        ]);
        $withTags = $this->service->boolValue($request->withTags);

        $post = null;

        if ($withTags) {
            $post = Post::with('tags:id,name')->find($id);
        } else {
            $post = Post::find($id);
        }

        if ($post === null) {
            return response()->json('Post does not exist', Response::HTTP_NOT_FOUND);
        }
        $this->authorize('view', $post);

        return response()->json(['data' => $post], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title'         => 'string|nullable',
            'content'       => 'required|string',
            'collection_id' => 'integer|nullable',
            'description'   => 'string|nullable',
            'tags'          => 'array|nullable',
            'tags.*'        => 'integer',
        ]);

        if (isset($request->collection_id)) {
            $collection = Collection::findOrFail($request->collection_id);
            if (Auth::user()->id !== $collection->user_id) {
                return response()->json('Not authorized', Response::HTTP_FORBIDDEN);
            }
        }

        $post = $this->service->store(
            $request->title,
            $request->content,
            $request->collection_id,
            $request->description,
            $request->tags,
            Auth::user()->id
        );

        return response()->json(['data' => $post], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {

        $validatedData = $this->validate($request, [
            'title'            => 'string|nullable',
            'content'          => 'string|nullable',
            'collection_id'    => 'integer|nullable',
            'is_uncategorized' => 'boolean|nullable',
            'tags'             => 'array|nullable',
            'tags.*'           => 'integer|required_with:tags',
            'order'            => 'integer|nullable',
            'is_archived'      => 'boolean|nullable'
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
            // post wants to be positioned somewhere else
            // staying in the same collection as before
            $newOrder = $validatedData['order'];

            // check authenticity of order
            if (!Post::where('collection_id', $post->collection_id)->where('order', $newOrder)->exists()) {
                $newOrder = Post::where('collection_id', $post->collection_id)->max('order');
                $newValues['order'] = $newOrder;
            }

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

        if (isset($newValues['tags'])) {
            $this->service->saveTags($post->id, $newValues['tags']);
        }
        $post->tags = $post->tags()->get();

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
