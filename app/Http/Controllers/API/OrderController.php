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

        // Ensure only admin can access all orders, else show only user's own orders
        if ($user->role === 'admin') {
            // Admin can see all orders
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
            // Customer can only see their own orders
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
            'status' => 'required|string',
        ]);

        $validatedData['date'] = Carbon::now()->toDateString();
        $validatedData['user_id'] = auth()->id();
        $validatedData['user_name'] = auth()->user()->name;

        $product = Product::findOrFail($validatedData['product_id']);
        $validatedData['price'] = $product->price;
        $validatedData['amount'] = $validatedData['price'] * $validatedData['quantity'];

        $order = Order::create($validatedData);
        return response()->json($order->load('user:id,name'), 201);
    }

    public function show($id): JsonResponse
    {
        return response()->json(Order::with('user')->findOrFail($id));
    }

    public function update(Request $request, $id): JsonResponse
{
    $user = auth()->user();

    // Find the order
    $order = Order::findOrFail($id);

    // Ensure only admin can update the order
    if ($user->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Validate the request data for only the 'status' field with a custom error message
    $validatedData = $request->validate([
        'status' => 'required|string|in:pending,on the way,delivered,completed', // List of allowed values
    ], [
        'status.in' => 'Invalid status. Please use one of the following: pending, on the way, delivered, completed.',
    ]);

    // Update only the status field
    $order->update([
        'status' => $validatedData['status'],
    ]);

    // Return a success message along with the updated order
    return response()->json([
        'success' => true,
        'message' => 'Order status updated successfully!',
        'order' => $order
    ]);
}

public function destroy($id): JsonResponse
{
    $user = auth()->user();

    // Ensure only admin can delete the order
    if ($user->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Find the order
    $order = Order::findOrFail($id);

    // Delete the order
    $order->delete();

    // Return a success message
    return response()->json(['message' => 'Order cancelled successfully']);
}

}


