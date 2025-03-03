<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserListController;

/*
|---------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Register your API routes here. These routes will be loaded by the
| RouteServiceProvider and all of them will be assigned to the "api" middleware group.
|
*/

// Protected route for authenticated users
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum'); // Protected route
});

// Protect routes under authentication
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/list', [UserListController::class, 'index'])->middleware('admin');

});
