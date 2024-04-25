<?php

namespace App\Http\Controllers;

use App\Http\Resources\PrivateShareResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\PrivateShare;
use App\Models\Collection;
use Illuminate\Http\Response;

class PrivateShareController extends Controller
{

    public function index(Request $request)
    {
        $this->validate($request, [
            'collection_id' => 'integer|nullable'
        ]);

        $shares = PrivateShare
            ::where('created_by', Auth::id())
            ->orWhere('user_id', Auth::id());
        if (empty($shares)) {
            return response()->json(['data' => null]);
        }

        if (!empty($request->collection_id)) {

            $collection = Collection::find($request->collection_id);
            if (empty($collection))
                return response()->json('Collection not found', Response::HTTP_NOT_FOUND);

            $this->authorize('view', $collection);

            $collection_ids = $collection->ancestorsAndSelf()->pluck('id');
            $shares = PrivateShare
                ::where('created_by', Auth::id())
                ->whereIn('collection_id', $collection_ids)
                ->orWhere(function ($query) use ($collection_ids) {
                    $query->where('user_id', Auth::id())
                        ->whereIn('collection_id', $collection_ids);
                });
        }

        return response()->json([
            'data' => PrivateShareResource::collection($shares->get())
        ], Response::HTTP_OK);
    }

    public function show(Request $request, int $id)
    {

        $share = PrivateShare::find($id);

        if (empty ($share)) {
            return response()->json('Share not found', Response::HTTP_NOT_FOUND);
        }
        if (
            Auth::id() !== $share->user_id &&
            Auth::id() !== $share->created_by
        ) {
            return response()->json('', Response::HTTP_FORBIDDEN);
        }
        return response()->json([
            'data' => new PrivateShareResource($share)
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {

        $request->validate([
            'collection_id' => 'required|integer',
            'user_id' => 'required|integer',
        ]);

        $collection = Collection::find($request->collection_id);
        if (!$collection) {
            return response()->json('Collection not found', Response::HTTP_NOT_FOUND);
        }
        if ($request->user_id === Auth::user()->id) {
            return response()->json('Impossible request', Response::HTTP_BAD_REQUEST);
        }

        $this->authorize('share', $collection);

        if (
            PrivateShare::where('collection_id', $collection->id)
                ->where('user_id', $request->user_id)
                ->exists()
        ) {
            return response()->json('Share already exists', Response::HTTP_BAD_REQUEST);
        }

        $share = PrivateShare::create([
            'collection_id' => $request->collection_id,
            'user_id' => $request->user_id,
            'created_by' => Auth::id()
        ]);

        $collection->descendantsAndSelf()->update(['is_being_shared' => true]);

        return response()->json([
            'data' => new PrivateShareResource($share)
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'collection_id' => 'integer',
            'user_id' => 'integer'
        ]);

        $share = PrivateShare::find($id);
        if (!$share) {
            return response()->json('Share not found', Response::HTTP_NOT_FOUND);
        }

        $this->authorize('update', $share);

        if (isset ($request->user_id)) {
            if ($request->user_id === Auth::id()) {
                return response()->json('Impossible request', Response::HTTP_BAD_REQUEST);
            }
            if (!User::where('id', $request->user_id)->exists()) {
                return response()->json('User not found', Response::HTTP_BAD_REQUEST);
            }
        }

        if (isset ($request->collection_id)) {
            $collection = Collection::find($request->collection_id);
            if (!$collection) {
                return response()->json(
                    'Collection not found',
                    Response::HTTP_NOT_FOUND
                );
            };
            $this->authorize('update', $collection);

            if ($collection->id !== $share->collection_id) {
                $collection->update(['is_being_shared' => true]);
                if (PrivateShare::where('collection_id', $share->collection_id)->count() === 1)
                    Collection::find($share->collection_id)
                        ->update(['is_being_shared' => false]);
            }

        }

        $share->update($validated);

        return response()->json(['data' => $share], 200);
    }

    public function destroy($id)
    {

        $share = PrivateShare::find($id);

        if (!$share) {
            return response()->json('PrivateShare not found.', Response::HTTP_NOT_FOUND);
        }

        $this->authorize('delete', $share);
        $share->delete();

        $sharedCollection = Collection::find($share->collection_id);
        $ancestorAndSelfIds = $sharedCollection->ancestorsAndSelf()->pluck('id');
        if (!PrivateShare::whereIn('collection_id', $ancestorAndSelfIds)->exists()) {
            $highestSharedDescendant = $sharedCollection
                ->descendants()
                ->join('private_shares', 'collection_id', '=', 'collections.id')
                ->orderBy('left')
                ->first();
            if (empty($highestSharedDescendant)) {
                $sharedCollection
                    ->descendantsAndSelf()
                    ->update(['is_being_shared' => false]);
            } else if ($sharedCollection->depth === 0) {
                $sharedCollection->update(['is_being_shared' => false]);
                Collection::where('root_collection_id', $sharedCollection->id)
                    ->where(function ($query) use ($highestSharedDescendant) {
                        $query->where('left', '<', $highestSharedDescendant->left)
                            ->orWhere('right', '>', $highestSharedDescendant->right);
                    })
                    ->update(['is_being_shared' => false]);
            } else {
                Collection::where('root_collection_id', $highestSharedDescendant->root_collection_id)
                    ->where(function ($query) use ($sharedCollection, $highestSharedDescendant) {
                        $query->whereBetween('left', [$sharedCollection->left, $highestSharedDescendant->left])
                            ->orWhereBetween('right', [$highestSharedDescendant->right, $sharedCollection->right]);
                    })
                    ->update(['is_being_shared' => false]);
            }
        }

        return response()->json('', 204);
    }

}
