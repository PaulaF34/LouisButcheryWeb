<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Requests\OrderListRequest;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(OrderListRequest $request): JsonResponse
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $orders = Order::with(['user:id,name'])
                ->when($request->status, function ($query, $status) {
                    return $query->where('status', 'like', "%{$status}%");
                })
                ->when($request->user_name, function ($query, $userName) {
                    return $query->whereHas('user', function ($query) use ($userName) {
                        $query->where('name', 'like', "%{$userName}%");
                    });
                })
                ->paginate($request->per_page ?? 10);  // Default to 10 per page
        } else {
            $orders = Order::with(['user:id,name'])
                ->where('user_id', $user->id)
                ->when($request->status, function ($query, $status) {
                    return $query->where('status', 'like', "%{$status}%");
                })
                ->paginate($request->per_page ?? 10);  // Default to 10 per page
        }

        return response()->json([
            'success' => true,
            'message' => 'Order list retrieved successfully.',
            'orders' => $orders
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'status' => 'nullable|string',  // Make status nullable because we'll set it to pending by default
        ]);

        $validatedData['status'] = $validatedData['status'] ?? 'pending'; // If status is null, set it to "pending"
        $validatedData['date'] = Carbon::now()->toDateString();
        $validatedData['user_id'] = auth()->id();
        $validatedData['user_name'] = auth()->user()->name;

        $product = Product::findOrFail($validatedData['product_id']);

        // Check if there's enough stock
        if ($product->stock < $validatedData['quantity']) {
            return response()->json(['message' => 'Not enough stock available.'], 400);
        }

        $validatedData['price'] = $product->price;
        $validatedData['amount'] = $validatedData['price'] * $validatedData['quantity'];

        $order = Order::create($validatedData);

        // Decrease the stock of the product
        $product->stock -= $validatedData['quantity'];
        $product->save();

        return response()->json($order->load('user:id,name'), 201);
    }

    public function show($id): JsonResponse
    {
        return response()->json(Order::with('user')->findOrFail($id));
    }

    public function update(Request $request, $id): JsonResponse
    {
        $user = auth()->user();
        $order = Order::findOrFail($id);

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'status' => 'required|string|in:pending,on the way,delivered,completed,canceled',
        ], [
            'status.in' => 'Invalid status. Please use one of the following: pending, on the way, delivered, completed, canceled.',
        ]);

        $order->update([
            'status' => $validatedData['status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully!',
            'order' => $order
        ]);
    }

    // Method to update the quantity of an item in an order
    public function updateQuantity(Request $request, $id): JsonResponse
    {
        $user = auth()->user();

        // Ensure only admin can update the quantity
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Find the order
        $order = Order::findOrFail($id);

        // Validate the new quantity
        $validatedData = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($order->product_id);

        // Check if there's enough stock for the new quantity
        if ($product->stock < $validatedData['quantity']) {
            return response()->json(['message' => 'Not enough stock available.'], 400);
        }

        // Calculate the new amount
        $order->quantity = $validatedData['quantity'];
        $order->amount = $product->price * $validatedData['quantity'];

        // Save the updated order
        $order->save();

        // Update product stock
        $product->stock -= $validatedData['quantity'];
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Order quantity updated successfully!',
            'order' => $order
        ]);
    }

    // Method to remove an item from the order
    public function removeItem($id): JsonResponse
    {
        $user = auth()->user();

        // Ensure only admin can remove items
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Find the order
        $order = Order::findOrFail($id);

        // Revert the stock of the product
        $product = Product::findOrFail($order->product_id);
        $product->stock += $order->quantity;
        $product->save();

        // Delete the order item
        $order->delete();

        return response()->json(['message' => 'Item removed from order successfully']);
    }

    public function destroy($id): JsonResponse
{
    $user = auth()->user();

    // Ensure only admin can cancel orders
    if ($user->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Find the order
    $order = Order::findOrFail($id);

    // Check if the order's status is 'pending'
    if ($order->status !== 'pending') {
        return response()->json(['message' => 'Only pending orders can be canceled'], 400);
    }

    // Revert the stock of the product based on the order quantity
    $product = Product::findOrFail($order->product_id); // Get the associated product
    $product->stock += $order->quantity; // Increment the stock by the order's quantity
    $product->save();

    // Set the order status to 'canceled'
    $order->status = 'canceled';
    $order->save();

    return response()->json(['message' => 'Order canceled and stock reverted successfully']);
}
}
