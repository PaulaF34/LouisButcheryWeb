<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function submitReview(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);

        // Get the authenticated user ID
        $userId = Auth::id();

        // Create a new review
        $review = new Review();
        $review->product_id = $request->product_id;
        $review->rating = $request->rating;
        $review->comment = $request->comment;
        $review->user_id = $userId;
        $review->save();

        // Return a success response
        return response()->json([
            'message' => 'Review submitted successfully.',
            'review' => $review
        ], 201);
    }

    // Get Reviews for a product
    public function getReviews($productId)
    {
        // Retrieve reviews for the given product
        $reviews = Review::where('product_id', $productId)->get();

        // Return reviews or a message if no reviews exist
        if ($reviews->isEmpty()) {
            return response()->json(['message' => 'No reviews found for this product.'], 404);
        }

        return response()->json($reviews);
    }
}
