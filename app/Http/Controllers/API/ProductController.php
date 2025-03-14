<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        // Retrieve all products
        $products = Product::all();
        return response()->json($products);
    }

    public function show($id)
    {
        // Retrieve the product by ID
        $product = Product::findOrFail($id);  // Will throw a 404 error if not found
        return response()->json($product);
    }

    public function store(Request $request)
    {
        // Ensure the user is authenticated and is an admin
        $user = auth()->user();
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Validate incoming request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:100',
            'image' => 'nullable|string', // Can be a URL or base64
            'price' => 'required|numeric',
            'stock' => 'required|integer',
        ]);

        // Create a new product using validated data
        $product = Product::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully!',
            'product' => $product
        ], 201); // Return the created product with a success message
    }

    public function update(Request $request, $id)
    {
        // Ensure the user is authenticated and is an admin
        $user = auth()->user();
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Validate incoming request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:100',
            'image' => 'nullable|string', // Can be a URL or base64
            'price' => 'required|numeric',
            'stock' => 'required|integer',
        ]);

        // Find the product by ID
        $product = Product::findOrFail($id);

        // Update the product with validated data
        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully!',
            'product' => $product
        ], 200);  // Return the updated product with success message
    }


    public function destroy($id)
{
    // Ensure the user is authenticated and is an admin
    $user = auth()->user();
    if ($user->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Find the product by ID and delete it
    $product = Product::findOrFail($id);
    $product->delete();

    return response()->json(['message' => 'Product deleted successfully.']);
}


    public function search($name)
    {
        // Search for products by name
        $products = Product::where('name', 'like', '%' . $name . '%')->get();
        return response()->json($products);
    }

    public function updateStock(Request $request, $id)
{
    // Ensure the user is authenticated and is an admin
    $user = auth()->user();
    if ($user->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Find the product by ID
    $product = Product::findOrFail($id);

    // Validate the stock input
    $validated = $request->validate([
        'stock' => 'required|integer|min:0',  // Ensure stock is a non-negative integer
    ]);

    // Update the stock and save the product
    $product->stock = $validated['stock'];
    $product->save();

    return response()->json([
        'message' => 'Stock updated successfully',
        'product' => $product
    ]);
}

}
