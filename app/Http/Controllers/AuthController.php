<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{

    public function login(Request $request)
    {

        $token = Auth::attempt($request->only('email', 'password'));

        if (!$token) {
            return response()->json('Invalid login credentials', 400);
        }

        $data = [
            "token" => [
                "access_token" => $token,
                "token_type"   => 'Bearer',
                "expire"       => (int) config('jwt.ttl')
            ]
        ];
        return response()->json(compact('data'));
    }

    public function me(Request $request)
    {
        return response()->json(['data' => $request->user()]);
    }

    public function refresh(Request $request)
    {
        if ($request->bearerToken() === 'null') {
            return response()->json('', Response::HTTP_BAD_REQUEST);
        }

        try {
            $data = [
                "token" => [
                    "access_token" => Auth::refresh(),
                    "token_type"   => 'Bearer',
                    "expire"       => (int) config('jwt.ttl')
                ]
            ];
            return response()->json(compact('data'));
        } catch (JWTException $e) {
            return response()->json('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function logout()
    {
        Auth::logout();
        // Auth::user()->tokens()->where('id', 'device_name')->delete();
        return response()->json('', 204);
    }

    public function sendReset(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email'
        ]);

        $response = Password::broker()->sendResetLink(
            $request->only('email')
        );

        if ($response === Password::RESET_LINK_SENT) {
            return response()->json(trans('passwords.sent'), 200);
        } else if (trans($response)) {
            return response()->json(['error' => trans($response)], 500);
        } else {
            return response()->json(['error' => $response], 500);
        }
    }

    public function reset(Request $request)
    {
        $this->validate($request, [
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|confirmed',
        ]);

        $response = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($response === Password::PASSWORD_RESET) {
            return response()->json(trans('passwords.reset'), 200);
        } else if (trans($response)) {
            return response()->json(['error' => trans($response)], 500);
        } else {
            return response()->json(['error' => $response], 500);
        }
    }
}
