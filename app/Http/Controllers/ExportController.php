<?php

namespace App\Http\Controllers;

use App\Helpers\NetscapeBookmarkEncoder;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\HttpFoundation\Response;

class ExportController extends Controller
{
    public function index()
    {

        if (!is_writable(config('benotes.temporary_directory'))) {
            return response()->json('Missing write permission', Response::HTTP_BAD_GATEWAY);
        }

        $tempDirectory = (new TemporaryDirectory(config('benotes.temporary_directory')))
            ->name('export')
            ->force()
            ->create()
            ->empty();

        $path = $tempDirectory->path('export.html');
        $encoder = new NetscapeBookmarkEncoder(Auth()->user()->id);
        $encoder->encodeToFile($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }

}
