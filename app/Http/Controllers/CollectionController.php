<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Collection;

class CollectionController extends Controller
{

    public function index()
    {
        $collections = Collection::where('user_id', Auth::user()->id)->orderBy('name')->get();
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
            'name'    => 'required|string',
            'icon_id' => 'nullable|integer'
        ]);

        $collection = Collection::create([
            'name' => $request->name,
            'user_id' => Auth::user()->id,
            'icon_id' => $request->icon_id
        ]);

        return response()->json(['data' => $collection], 201);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name'    => 'required|string',
            'icon_id' => 'nullable|integer'
        ]);

        $collection = Collection::find($id);
        $this->authorize('update', $collection);

        $collection->name = $request->name;
        $collection->icon_id = $request->icon_id;
        $collection->save();

        return response()->json(['data' => $collection], 200);
    }

    public function destroy($id)
    {
        $collection = Collection::find($id);
        if (!$collection) {
            return response()->json('Collection not found.', 400);
        }
        $this->authorize('delete', $collection);
        $collection->delete();

        return response()->json('', 204);
    }
}
