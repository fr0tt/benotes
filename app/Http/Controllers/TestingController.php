<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class TestingController extends Controller
{

    public function __construct()
    {
        $this->middleware('testing');
    }

    public function user(Request $request)
    {
        $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        $user = User::factory()->create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'permission' => 7
        ]);
        return response()->json(['data' => $user]);
    }

    public function setup()
    {
        Artisan::call('config:clear');
        Artisan::call('migrate:fresh');
        return response()->json(null, Response::HTTP_OK);
    }

    public function teardown()
    {
        Artisan::call('config:clear');
        return response()->json(null, Response::HTTP_OK);
    }
}
