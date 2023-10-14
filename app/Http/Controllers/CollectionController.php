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
            'nested' => 'nullable',
        ]);

        $request->nested = boolval($request->nested);
        $collections = null;

        if ($request->nested) {
            $collections = Collection::with('nested')
                ->where('user_id', Auth::user()->id)
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get();
        } else {
            $collections = Collection::where('user_id', Auth::user()->id)->orderBy('name')->get();
        }
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
            'name' => 'required|string',
            'parent_id' => 'nullable|integer',
            'icon_id' => 'nullable|integer'
        ]);

        $parent_id = null;
        if (isset($request->parent_id)) {
            $collection = Collection::find($request->parent_id);
            if (!$collection) {
                return response()->json('Collection not found', 404);
            }
            $this->authorize('inherit', $collection);
            $parent_id = $collection->id;
        }

        $collection = $this->service->store(
            $request->name,
            Auth::user()->id,
            $parent_id,
            $request->icon_id
        );

        return response()->json(['data' => $collection], 201);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'nullable|string',
            'icon_id' => 'nullable|integer',
            'parent_id' => 'nullable|integer',
            'is_root' => 'nullable|boolean',
        ]);

        //$request->is_root = boolval($request->is_root);

        $collection = Collection::find($id);
        $this->authorize('update', $collection);

        /*
        if (isset($request->name)) {
            $collection->name = $request->name;
        }
        if (isset($request->icon_id)) {
            $collection->icon_id = $request->icon_id;
        }
        */

        $parent_id = $request->parent_id;
        if (isset($request->parent_id)) {
            if ($collection->id === $request->parent_id)
                return response()->json('Not possible', Response::HTTP_BAD_REQUEST);
            $parent_collection = Collection::find($request->parent_id);
            if (!$parent_collection) {
                return response()->json('Collection not found', Response::HTTP_NOT_FOUND);
            }
            $this->authorize('inherit', $parent_collection);
            //$collection->parent_id = $parent_collection->id;
            $parent_id = $parent_collection->id;
        }

        /*
        if ($request->is_root) {
            $collection->parent_id = null;
        }

        $collection->save();
        */

        $collection = $this->service->update(
            $id,
            $request->name,
            $parent_id,
            boolval($request->is_root),
            $request->icon_id
        );

        return response()->json(['data' => $collection], 200);
    }

    public function destroy(Request $request, $id)
    {
        $this->validate($request, [
            'nested' => 'nullable',
        ]);

        $collection = Collection::find($id);
        if (!$collection) {
            return response()->json('Collection not found.', 400);
        }
        $this->authorize('delete', $collection);

        $this->service->delete($id, boolval($request->nested), Auth::id());

        return response()->json('', 204);
    }
}
