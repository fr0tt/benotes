<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Collection;
use App\Services\CollectionService;
use Symfony\Component\HttpFoundation\Response;

class CollectionController extends Controller
{

    private $service;

    public function __construct()
    {
        $this->service = new CollectionService();
    }

    public function index(Request $request)
    {
        $this->validate($request, [
            'nested'     => 'nullable',
            'withShared' => 'nullable'
        ]);

        $request->nested = boolval($request->nested);
        $request->withShared = boolval($request->withShared);
        $collections = Collection::where('user_id', Auth::user()->id)
            ->orderBy('name')
            ->get();
        if ($request->nested) {
            $collections = Collection::toNested($collections);
        }

        if ($request->withShared) {
            $composed['collections'] = $collections;
            $sharedCollections = $this->service->getSharedCollections(Auth::id());
            if ($request->nested)
                $sharedCollections = Collection::toNested($sharedCollections);
            $composed['shared_collections'] = $sharedCollections;
            $collections = $composed;
        }

        return response()->json(['data' => $collections]);
    }

    public function indexShared(Request $request)
    {
        $request->nested = boolval($request->nested);

        $collections = $this->service->getSharedCollections(Auth::id());
        if ($request->nested)
            $collections = Collection::toNested($collections);
        return response()->json(['data' => $collections]);
    }

    public function show($id)
    {
        if (!is_numeric($id)) {
            return response()->json('Requires an id', 400);
        }
        $collection = Collection::find($id);
        if (!$collection) {
            return response()->json('Collection not found', 404);
        }
        $this->authorize('view', $collection);
        return response()->json(['data' => $collection]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name'      => 'required|string',
            'parent_id' => 'nullable|integer',
            'icon_id'   => 'nullable|integer'
        ]);

        $parent_id = null;
        if (!empty($request->parent_id)) {
            $collection = Collection::find($request->parent_id);
            if (!$collection) {
                return response()->json('Collection not found', 404);
            }
            $this->authorize('inherit', $collection);
            $parent_id = $collection->id;
        }

        $collection = $this->service->store(
            $request->name,
            Collection::getOwner($request->parent_id),
            $parent_id,
            $request->icon_id
        );

        return response()->json(['data' => $collection], 201);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name'      => 'nullable|string',
            'icon_id'   => 'nullable|integer',
            'parent_id' => 'nullable|integer',
            'is_root'   => 'nullable|boolean',
        ]);

        $request->is_root = boolval($request->is_root);

        $collection = Collection::find($id);

        $this->authorize('update', $collection);

        if ($request->is_root) {
            $parent_id = null;
            $parent = null;
        } else if (!empty($request->parent_id)) {
            $parent_id = $request->parent_id;
            if ($collection->id === $parent_id)
                return response()->json('Not possible', Response::HTTP_BAD_REQUEST);
            $parent = Collection::find($parent_id);
            if (!$parent) {
                return response()->json('Collection not found', Response::HTTP_NOT_FOUND);
            }
            $this->authorize('inherit', $parent);
        } else {
            $parent_id = $collection->parent_id;
            $parent = Collection::find($parent_id);
        }

        if ($collection->parent_id !== $parent_id) {
            $this->authorize('move', [$collection, $parent]);
        }

        $collection = $this->service->update(
            $id,
            $request->name,
            $parent,
            $request->icon_id,
            Auth()->id()
        );

        return response()->json(['data' => $collection], 200);
    }

    public function destroy(Request $request, $id)
    {
        $collection = Collection::find($id);
        if (!$collection) {
            return response()->json('Collection not found.', 400);
        }
        $this->authorize('delete', $collection);

        $this->service->delete($collection);

        return response()->json('', 204);
    }

}
