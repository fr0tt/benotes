<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Share;
use App\Collection;

class ShareController extends Controller
{

    public function index(Request $request)
    {
        $this->validate($request, [
            'collection_id' => 'integer|nullable'
        ]);

        if (empty($request->collection_id)) {
            $shares = Share::where('created_by', Auth::user()->id)->get();
        } else {
            $shares = Share::where('created_by', Auth::user()->id)
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

        $this->authorize('share', Collection::findOrFail($request->collection_id));

        $share = new Share;
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

        $share = Share::find($id);
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
            // apparently updating validatedData seems no to work properly when it comes to boolean
            $share->is_active = $request->is_active;
        }

        $share->update($validatedData);

        return response()->json(['data' => $share], 200);
    }

    public function destroy($id)
    {
        $share = Share::find($id);
        
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
