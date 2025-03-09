<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Cart;
use App\Models\Payment;
use App\Models\Notification;
use App\Models\Review;
use App\Models\Chat;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/', function () {

    // $users1 = User::create( [
    //     'name' => 'John Doe',
    //     'address' => '123 Main St',
    //     'phone' => '555-1234',
    //     'email' => 'john@test.com',
    //     'password' => 'password',
    //     'role' => 'customer',
    // ] );
    // $users2 = User::create([
    //     'name' => 'Jane Li',
    //     'address' => '124 Main St',
    //     'phone' => '556-1235',
    //     'email' => 'Jane@test.com',
    //     'password' => '123456',
    //     'role' => 'admin',
    // ] );
    // dd($users2);

// $products1 = Product::create([
//     'name' => 'Mafroome',
//     'description' => '500 g Beef Meat Mafroome',
//     'price' => 10.99,
//     'image' => 'C:\Users\User\Desktop\575.webp',
//     'category' => 'Beef',
//     'stock' => 20,
// ]);

// $products2 = Product::create([
//     'name' => 'Mafroome',
//     'description' => '1000 g Chicken Meat Mafroome',
//     'price' => 15.99,
//     'image' => 'C:\Users\User\Desktop\575.webp',
//     'category' => 'Beef',
//     'stock' => 20,
// ]);

// $products3 = Product::create([
//     'name' => 'Steak',
//     'description' => '200 g Beef Meat Steak',
//     'price' => 20.99,
//     'image' => 'C:\Users\User\Desktop\bs.jpeg',
//     'category' => 'Beef',
//     'stock' => 20,
// ]);
// dd($products3);

    // $orders1 = Order::create([
    //     'date' => now(),
    //     'user_id' => 1,
    //     'product_id' => 1,
    //     'quantity' => 2,
    //     'price' => 10.99,
    //     'amount' => 21.98,
    //     'status' => 'pending',
    // ]);

    // dd($orders1);

    // $carts1 = Cart::create([
    //     'user_id' => 1,
    //     'product_id' => 1,
    //     'quantity' => 2,
    //     'price' => 10.99,
    //     'amount' => 21.98,
    // ]);

    // $carts2 = Cart::create([
    //     'user_id' => 1,
    //     'product_id' => 3,
    //     'quantity' => 4,
    //     'price' => 20.99,
    //     'amount' => 83.96,
    // ]);

    // dd($carts1);

    // $payments1 = Payment::create([
    //     'order_id' => 1,
    //     'user_id' => 1,
    //     'payment_method' => 'credit card',
    //     'transaction_id' => '123456',
    //     'amount' => 21.98,
    //     'status' => 'paid',
    // ]);

    // dd($payments1);

    // $notifications1 = Notification::create([
    //     'user_id' => 1,
    //     'title' => 'Order Placed',
    //     'message' => 'Your order has been placed',
    //     'status' => 'unread',
    // ]);

    // $notifications2 = Notification::create([
    //     'user_id' => 1,
    //     'title' => 'Order Shipped',
    //     'message' => 'Your order has been shipped',
    //     'status' => 'unread',
    // ]);

    // dd($notifications2);

    // $reviews1 = Review::create([
    //     'user_id' => 1,
    //     'product_id' => 1,
    //     'rating' => 5,
    //     'comment' => 'Great product',
    // ]);

    // $reviews2 = Review::create([
    //     'user_id' => 1,
    //     'product_id' => 3,
    //     'rating' => 4,
    //     'comment' => 'Good product',
    // ]);

    // dd($reviews2);

    // $chats1 = Chat::create([
    //     'user_id' => 1,
    //     'message' => 'Hello',
    //     'sender' => 'customer',
    // ]);


    //Find all products
    // $products = Product::all();
    // dd($products);

    //Find all users where role is admin
    // $users = User::where('role', 'admin')->get();
    // dd($users);

    //Find all orders done by user with id 1 where status is pending
    // $orders = Order::where('user_id', 1)->where('status', 'pending')->get();
    // dd($orders);

    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
