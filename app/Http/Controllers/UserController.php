<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;

use App\User;

class UserController extends Controller
{

    public function index()
    {
        $users = User::all();
        return response()->json(['data' => $users], 200);
    }

    public function show(int $id)
    {
        $user = User::find($id);
        if ($user === null) {
            return response()->json('User not found', 404);
        }
        return response()->json(['data' => $user], 200);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name'     => 'required|string',
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $this->authorize('create', User::class);
        
        $alreadyExistingUser = User::where('email', $request->email)->first();

        if (!empty($alreadyExistingUser)) {
            return response()->json('Email is already in use.', 400);
        }

        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email; 
        $user->password = Hash::make($request->password);
        $user->permission = 7;
        $user->save();

        return response()->json(['data' => $user], 201);
    }

    public function update(Request $request, int $id)
    { 
        $this->validate($request, [
            'name' => 'string',
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
        
        $user = User::find($id);
        if (!$user) {
            return response()->json('User not found.', 404);
        }
        $this->authorize('update', $user);
        
        $user->update($request->only('name', 'email'));

        if (Hash::check($request->password_old, $user->password)) {
            $user->update([
                'password' => Hash::make($request->password_new)
            ]);
        }

        return response()->json(['data' => $user], 200);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json('User not found.', 404);
        }
        $this->authorize('delete', User::class);
        $user->delete();

    }

}