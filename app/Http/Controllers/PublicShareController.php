<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\PublicShare;
use App\Models\Collection;
use Illuminate\Http\Response;

class PublicShareController extends Controller
{

    public function index(Request $request)
    {
        $this->validate($request, [
            'collection_id' => 'integer|nullable'
        ]);

        if (empty($request->collection_id)) {
            $shares = PublicShare::where('created_by', Auth::user()->id)->get();
        } else {
            $shares = PublicShare::where('created_by', Auth::user()->id)
                ->where('collection_id', $request->collection_id)
                ->get();
        }

        return response()->json(['data' => $shares], 200);
    }

    public function store(Request $request)
    {

        $this->validate($request, [
            'token' => 'required|string',
            'collection_id' => 'required|integer',
            'is_active' => 'required|boolean'
        ]);

        $collection = Collection::findOrFail($request->collection_id);
        $this->authorize('share', $collection);

        if (PublicShare::where('collection_id', $collection->id)->exists()) {
            return response()->json('', Response::HTTP_BAD_REQUEST);
        }

        $share = new PublicShare;
        $share->token = $request->token;
        $share->collection_id = $request->collection_id;
        $share->permission = 4;
        $share->created_by = Auth::user()->id;
        $share->is_active = $request->is_active;
        $share->save();

        return response()->json(['data' => $share], 201);
    }

    public function update(Request $request, int $id)
    {
        $validatedData = $this->validate($request, [
            'token' => 'string|required',
            'collection_id' => 'integer|required',
            'is_active' => 'boolean|required'
        ]);

        $share = PublicShare::find($id);
        if (!$share) {
            return response()->json('Share not found', 404);
        }

        if (isset($request->collection_id)) {
            $collection = Collection::find($request->collection_id);
            if (!$collection) {
                return response()->json('Collection not found', 404);
            }
        } else {
            $collection = Collection::find($share->collection_id);
        }

        $this->authorize('share', $collection);

        if (isset($request->is_active)) {
            $validatedData['is_active'] = boolval($request->is_active);
        }

        $share->update($validatedData);

        return response()->json(['data' => $share], 200);
    }

    public function destroy($id)
    {
        $share = PublicShare::find($id);

        if ($share) {
            $this->authorize('delete', $share);
            $share->delete();
            return response()->json('', 204);
        } else {
            return response()->json('Share not found.', 404);
        }
    }

    public function me()
    {
        return response()->json(['data' => Auth::guard('share')->user()]);
    }
}
