<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
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
    return view('welcome');
});
