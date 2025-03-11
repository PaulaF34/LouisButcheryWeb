<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserListController;
use App\Http\Controllers\API\PasswordResetController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\OrderController;




/*
|---------------------------------------------------------------------------
| API Routes
|---------------------------------------------------------------------------
| Register your API routes here. These routes will be loaded by the
| RouteServiceProvider and all of them will be assigned to the "api" middleware group.
|
*/

// Protected route for authenticated users
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum'); // Protected route
});

// Protect routes under authentication
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/list', [UserListController::class, 'index'])->middleware('admin');
});


Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

Route::prefix('products')->controller(ProductController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/{id}', 'show');
    Route::post('/new', 'store');
    Route::put('/update/{id}', 'update');
    Route::delete('/delete/{id}', 'destroy');
    Route::get('/search/{name}', 'search');
});

Route::prefix('orders')->group(function () {
    Route::post('/new', [OrderController::class, 'store']); // Create an order
    Route::get('/', [OrderController::class, 'index']); // Get all orders
    Route::get('/{id}', [OrderController::class, 'show']); // Get order details
    Route::put('/update/{id}', [OrderController::class, 'update']); // Update order status
    Route::delete('/cancel/{id}', [OrderController::class, 'destroy']); // Cancel an order
});
