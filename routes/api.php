<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;

/*
|---------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Register your API routes here. These routes will be loaded by the
| RouteServiceProvider and all of them will be assigned to the "api" middleware group.
|
*/

// Public route for user registration
Route::post('/register', [AuthController::class, 'register']);

// Public route for user login
Route::post('/login', [AuthController::class, 'login']);

// Protected route for authenticated users
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
