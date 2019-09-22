<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;

use App\User;

class UserController extends Controller
{

    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'name' => 'alpha_dash',
            'email' => 'email',
            'password_old' => 'string',
            'password_new' => 'string|required_with:password_old'
        ]);
            
        if (isset($request->email)) {
            $emailUser = User::where('email', $request->email)->first();
            if ($emailUser->id !== $id) {
                return response()->json('Email is already in use.', 400);
            }
        }

        if ($request->user()->id !== $id) {
            return response()->json('Not allowed', 403);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json('User not found.', 404);
        }
        
        $user->update(Input::only('name', 'email'));

        if (Hash::check($request->password_old, $user->password)) {
            $user->update([
                'password' => Hash::make($request->password_new)
            ]);
        }

        return response()->json(['data' => $user], 200);
    }

}