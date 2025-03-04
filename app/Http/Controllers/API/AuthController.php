<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;

class AuthController extends Controller{
    public function register(RegisterRequest $request)
{
    // Get the validated data from the request
    $validatedData = $request->validated();

    // Create the user with the validated data (password will be hashed by the model)
    $user = User::create($validatedData);

    // Generate an authentication token for the user
    $token = $user->createToken('authToken')->plainTextToken;

    // Return response with user details and token
    return response()->json([
        'success' => true,
        'message' => 'Successfully created user!',
        'user' => $user,
        'token' => $token,
    ], 201);
}

    public function login(LoginRequest $request)
    {
        // Attempt to authenticate with the given credentials
        if (!auth()->attempt($request->only('email', 'password'), $request->remember)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        // Get the authenticated user
        $user = auth()->user();

        // Generate a token (if using Laravel Sanctum or Passport)
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return user data with token
        return response()->json([
            'message' => 'Login successful!',
            'user' => $user, // Includes all fields from the `users` table
            'token' => $token,
        ]);
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
