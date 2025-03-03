<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller{
    public function register(Request $request)
    {
        $user = new User([
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => $request->password, // Model will hash it
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
    // Validate request data
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    // Find user by email
    $user = User::where('email', $request->email)->first();

    // Check if user exists and verify password using Hash::check()
    if ($user && Hash::check($request->password, $user->password)) {
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
