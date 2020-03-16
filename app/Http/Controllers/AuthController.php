<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $token = Auth::attempt($request->only('email', 'password'));

        if(!$token) {
            return response()->json('Invalid login credentials', 400);
        }

        $data = [
            "token" => [
                "access_token" => $token,
                "token_type"   => 'Bearer',
                "expire"       => (int) Auth::guard()->factory()->getTTL()
            ]
        ];
        return response()->json(compact('data'));
    }

    public function me(Request $request)
    {
        return response()->json(['data' => $request->user()]);
    }

    public function refresh()
    {
        $data = [
            "token" => [
                "access_token" => Auth::refresh(),
                "token_type"   => 'Bearer',
                "expire"       => (int) Auth::guard()->factory()->getTTL()
            ]
        ];
        return response()->json(compact('data'));
    }

    public function logout()
    {
        Auth::logout();
        return response()->json('', 204);
    }

}
