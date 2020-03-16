<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Collection;

class CollectionController extends Controller
{

    public function index()
    {
        $collections = Collection::where('user_id', Auth::user()->id)->get();
        return response()->json(['data' => $collections]);
    }

    public function show($id)
    {
        if (!is_numeric($id)) {
            return response()->json('Requires an id', 400);
        }
        $collection = Collection::findOrFail($id);
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
        ]);

        $collection = Collection::create([
            'name' => $request->name,
            'user_id' => Auth::user()->id
        ]);

        return response()->json(['data' => $collection], 201);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string',
        ]);

        $collection = Collection::find($id);
        $this->authorize('update', $collection);
        $collection->name = $request->name;
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
