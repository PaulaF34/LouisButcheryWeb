<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserListController;
use App\Http\Controllers\API\PasswordResetController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ChatController;


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
    Route::get('/search/{name}', 'search');

    // Apply authentication middleware to the store route (only accessible by admin)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/new', 'store');  // Only authenticated users can create a new product
        Route::put('/update/{id}', 'update');  // Only authenticated users can update products
        Route::delete('/delete/{id}', 'destroy');  // Only authenticated users can delete products
        Route::put('/update-stock/{id}', 'updateStock');  // Only authenticated users can update product stock
    });

});

Route::middleware(['auth:sanctum'])->group(function () {

    // Cart APIs
    Route::prefix('cart')->group(function () {
        Route::post('/add', [OrderController::class, 'addToCart']); // Add item to cart
        Route::get('/', [OrderController::class, 'getCart']); // Get cart items
        Route::delete('/remove/{id}', [OrderController::class, 'removeFromCart']); // Remove item from cart
        Route::post('/clear', [OrderController::class, 'clearCart']); // Clear cart
    });

    // Order APIs
    Route::prefix('orders')->group(function () {
        Route::post('/checkout', [OrderController::class, 'checkout']); // Create an order from cart
        Route::get('/', [OrderController::class, 'index']); // Get all orders
        Route::get('/{id}', [OrderController::class, 'show']); // Get order details
        Route::put('/update/{id}', [OrderController::class, 'update']); // Update order status
        Route::delete('/cancel/{id}', [OrderController::class, 'destroy']); // Cancel an order
        Route::put('/quantity/{id}', [OrderController::class, 'updateQuantity']); // Update quantity of an item in an order
        Route::delete('/item/{order_id}/{product_id}', [OrderController::class, 'removeItemFromOrder']); // Remove an item from the order
    });
});

Route::prefix('payment')->middleware('auth:sanctum')->group(function () {
    // Route for initiating payment
    Route::post('/initiate', [PaymentController::class, 'initiatePayment']);

    // Route for confirming payment
    Route::post('/confirm', [PaymentController::class, 'confirmPayment']);
});

Route::middleware('auth:sanctum')->group(function () {

    // Route to send a notification, only accessible by admin
    Route::post('/notifications/send', [NotificationController::class, 'sendNotification']);

    // Route to get notifications
    // A user can only see their own notifications, or all notifications if they are an admin
    Route::get('/notifications', [NotificationController::class, 'getNotifications']);

    // Route to delete a notification, ensure the user has permission
    Route::delete('/notifications/{id}', [NotificationController::class, 'deleteNotification']);
});

Route::middleware('auth:sanctum')->post('/review/submit', [ReviewController::class, 'submitReview']);
Route::get('/review/{productId}', [ReviewController::class, 'getReviews']);

Route::middleware('auth:sanctum')->group(function () {
    // Start Chat Route (only for authenticated users)
    Route::post('/chat/start', [ChatController::class, 'startChat']);

    // Get Chat History Route (only for authenticated users)
    Route::get('/chat/{chatId}', [ChatController::class, 'getChatHistory']);

    // Admin Routes
    Route::post('/admin/chat/{chatId}/send', [ChatController::class, 'adminSendMessage']); // Admin Respond

});
