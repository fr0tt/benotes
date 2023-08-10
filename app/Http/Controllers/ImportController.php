<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Helpers\NetscapeBookmarkDecoder;
use App\Services\PostService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ImportController extends Controller
{

    private $service;

    public function __construct()
    {
        $this->service = new PostService();
    }

    public function store(Request $request)
    {

        $this->validate($request, [
            'file' => 'file|mimetypes:text/html|required',
        ]);

        $collection = Collection::firstOrCreate([
            'name'    => Collection::IMPORTED_COLLECTION_NAME,
            'user_id' => Auth()->user()->id
        ]);

        $parser = new NetscapeBookmarkDecoder(Auth()->user()->id);
        $parser->parseFile($request->file('file'), $collection->id);

        return response()->json('', Response::HTTP_CREATED);
    }

}
