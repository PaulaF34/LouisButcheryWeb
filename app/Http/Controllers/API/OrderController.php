<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Requests\OrderListRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    //Cart Functions
/**
 *
 * Add item to cart
 */
public function addToCart(Request $request)
{
    // Validate input data
    $request->validate([
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
    ]);

    $cartItems = [];
    $totalAmount = 0;  // Initialize the total amount variable

    // Get the authenticated user
    $user = Auth::user();

    foreach ($request->items as $item) {
        $product = Product::findOrFail($item['product_id']);

        // Check if requested quantity is available in stock
        if ($item['quantity'] > $product->stock) {
            return response()->json([
                'message' => "Insufficient stock for product ID: {$item['product_id']}"
            ], 400);
        }

        $price = $product->price;
        $amount = $price * $item['quantity'];

        // Create cart item without order_id (leave it null)
        $cartItem = Cart::create([
            'user_id' => $user->id,
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'price' => $price,
            'amount' => $amount,
            'order_id' => null,  // Leave order_id null
        ]);

        // Add the amount to the total sum
        $totalAmount += $cartItem->amount;

        $cartItems[] = $cartItem;
    }

    return response()->json([
        'message' => 'Item(s) added to cart',
        'cart' => $cartItems,
        'total_amount' => $totalAmount,  // Include the total sum in the response
    ], 200);
}
public function getCart()
{
    $user = Auth::user();

    if ($user->role === 'admin') {
        // Admin can view all carts
        $cart = Cart::with('product', 'user')->get();
    } else {
        // Regular user can view only their own cart
        $cart = Cart::where('user_id', $user->id)->with('product')->get();
    }

    return response()->json(['cart' => $cart], 200);
}
public function removeFromCart($id)
{
    $user = Auth::user();

    // Ensure user can only remove their own cart items
    $cartItem = Cart::where('id', $id)->where('user_id', $user->id)->first();

    if (!$cartItem) {
        return response()->json(['message' => 'Item not found or unauthorized'], 403);
    }

    $cartItem->delete();
    return response()->json(['message' => 'Item removed from cart'], 200);
}
public function clearCart()
{
    $user = Auth::user();

    // Check if the user has items in their cart
    $cartItems = Cart::where('user_id', $user->id);

    if (!$cartItems->exists()) {
        return response()->json(['message' => 'Cart is already empty'], 200);
    }

    // Delete only the user's cart items
    $cartItems->delete();

    return response()->json(['message' => 'Cart cleared'], 200);
}

//Order Functions
    // ✅ Get all orders (Admin gets all, user gets own orders)
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $orders = Order::with('user:id,name')
            ->when($user->role !== 'admin', fn($query) => $query->where('user_id', $user->id))
            ->when($request->status, fn($query, $status) => $query->where('status', $status))
            ->paginate($request->per_page ?? 10);

        return response()->json(['success' => true, 'orders' => $orders]);
    }

    // ✅ Checkout: Convert Cart into Order
    public function checkout(Request $request)
{
    // Get the authenticated user
    $user = Auth::user();

    // Get all cart items for the user with no order_id (meaning they haven't been linked to an order)
    $cartItems = Cart::where('user_id', $user->id)
                     ->whereNull('order_id')
                     ->get();

    // If no items in the cart, return an error
    if ($cartItems->isEmpty()) {
        return response()->json([
            'message' => 'No items in the cart to checkout.',
        ], 400);
    }

    // Calculate the total amount for the order
    $totalAmount = $cartItems->sum('amount');

    // Create a new order
    $order = Order::create([
        'status' => 'pending',  // Order status at the time of checkout
        'total_price' => $totalAmount,
        'user_id' => $user->id,
    ]);

    // Update the cart items with the order_id
    foreach ($cartItems as $cartItem) {
        $cartItem->update(['order_id' => $order->id]);
    }

    // Return the order details as response
    return response()->json([
        'message' => 'Checkout successful',
        'order' => $order,
        'cart' => $cartItems,
        'total_amount' => $totalAmount,
    ], 200);
}
    // ✅ Get single order details
    public function show($id): JsonResponse
    {
        // Get the authenticated user
        $user = Auth::user();

        // Find the order by ID
        $order = Order::with('user')->findOrFail($id);

        // Check if the user is the owner of the order or if they are an admin
        if ($order->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'message' => 'You do not have permission to view this order.'
            ], 403);  // Forbidden
        }

        return response()->json($order);
    }

    // ✅ Update order status (Admin only)
    public function update(Request $request, $id): JsonResponse
    {
        $user = auth()->user();
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|string|in:pending,on the way,delivered,completed,canceled',
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully!',
            'order' => $order
        ]);
    }

    // ✅ Cancel order & restore stock (Admin only)
    public function destroy($id): JsonResponse
    {
        $user = auth()->user();
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $order = Order::findOrFail($id);
        $order->load('carts.product');

        // Restore stock for each item
        foreach ($order->carts as $cart) {
            $cart->product->increment('stock', $cart->quantity);
        }

        $order->update(['status' => 'canceled']);

        return response()->json(['message' => 'Order canceled and stock restored.']);
    }

    public function updateQuantity(Request $request, $cartId)
    {
        // Validate the request input
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        // Find the cart item by its cart ID
        $cartItem = Cart::find($cartId);

        // Check if the cart item exists
        if (!$cartItem) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        // Check if the product_id matches the cart item
        if ($cartItem->product_id != $request->product_id) {
            return response()->json(['message' => 'Product ID mismatch'], 400);
        }

        // Find the product to get its stock and price
        $product = Product::find($request->product_id);

        // Check if there is enough stock
        if ($product->stock < $request->quantity) {
            return response()->json(['message' => 'Not enough stock available'], 400);
        }

        // Calculate the difference in stock
        $stockDifference = $request->quantity - $cartItem->quantity;

        // Update the stock of the product
        $product->stock -= $stockDifference;

        // Save the updated product stock
        $product->save();

        // Update the cart item quantity
        $cartItem->quantity = $request->quantity;

        // Recalculate the amount based on the updated quantity and product price
        $cartItem->amount = $cartItem->quantity * $product->price;

        // Save the updated cart item
        $cartItem->save();

        // Recalculate the total price of the order manually
        $orderTotal = Cart::where('order_id', $cartItem->order_id)->sum('amount');

        // Update the order total price
        $order = Order::find($cartItem->order_id);
        $order->total_price = $orderTotal;
        $order->save();

        return response()->json([
            'message' => 'Cart updated successfully',
            'cartItem' => $cartItem,
            'orderTotal' => $orderTotal
        ]);
    }

    public function removeItemFromOrder($orderId, $productId)
{
    // Find the cart item based on order_id and product_id
    $cartItem = Cart::where('order_id', $orderId)
                    ->where('product_id', $productId)
                    ->first();

    // Check if the cart item exists
    if (!$cartItem) {
        return response()->json(['message' => 'Cart item not found'], 404);
    }

    // Revert the stock of the product (increase stock)
    $product = Product::find($productId);
    if ($product) {
        $product->stock += $cartItem->quantity;  // Add back the quantity to the stock
        $product->save();
    }

    // Delete the cart item
    $cartItem->delete();

    // Recalculate the total price of the order after the item removal
    $orderTotal = Cart::where('order_id', $orderId)->sum('amount');

    // Update the order total price
    $order = Order::find($orderId);
    if ($order) {
        $order->total_price = $orderTotal;
        $order->save();
    }

    return response()->json([
        'message' => 'Item removed successfully',
        'orderTotal' => $orderTotal
    ]);
}
}
