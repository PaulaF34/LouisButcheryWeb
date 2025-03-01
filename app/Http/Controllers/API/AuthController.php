<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller{
  public function register(Request $request)
  {
    // dd($request->all());

    $user = new User([
        'name' => $request->name,
        'address' => $request->address,
        'phone' => $request->phone,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'role' => 'customer',
    ]);
    $user->save();

    $token = $user->createToken('authToken')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Successfully created user!',
        'user' => $user,
        'token' => $token,
    ], 201);
}

  public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if(auth()->attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => true,
                'message' => 'Successfully logged in!',
                'user' => auth()->user(),
                'token' => $user->createToken('authToken')->plainTextToken,
            ], 200);
        }
        else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }
    }
}
