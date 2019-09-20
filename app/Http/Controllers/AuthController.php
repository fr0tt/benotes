<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use App\User;

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

    public function register(Request $request) 
    {
        $this->validate($request, [
            'name' => 'required|alpha_dash',
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (env('ALLOW_USER_REGISTRATION') === false) {
            return response()->json('Not allowed.', 403);
        }

        $alreadyExistingUser = User::where('email', $request->email)->first();

        if (!empty($alreadyExistingUser)) {
            return response()->json('Email is already in use.', 400);
        }
        
        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['data' => $user], 200);
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
