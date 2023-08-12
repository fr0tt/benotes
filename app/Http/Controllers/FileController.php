<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Symfony\Component\HttpFoundation\Response;

class FileController extends Controller
{

    public function store(Request $request)
    {

        $this->validate($request, [
            'file' => 'image|required',
        ]);

        $path = $request->file('file')->store('attachments');

        Image::make(Storage::path($path))
            ->resize(1600, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->interlace()
            ->save();

        return response()->json([
            'data' => [
                'path' => Storage::url($path)
            ]
        ], Response::HTTP_CREATED);
    }

}
