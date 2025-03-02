<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller{
  public function register(Request $request)
  {
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
    // Validate incoming request
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    // Attempt to find user and validate credentials
    $user = User::where('email', $request->email)->first();

    // If user exists and password matches
    if ($user && password_verify($request->password, $user->password)) {
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged in!',
            'user' => $user,
            'token' => $token,
        ], 200);
    } else {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized',
        ], 401);
    }
}


    public function logout(Request $request)
{
    $request->user()->tokens()->delete(); // This deletes all user tokens

    return response()->json([
        'success' => true,
        'message' => 'Successfully logged out!',
    ], 200);
}

}
