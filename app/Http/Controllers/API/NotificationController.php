<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Send a notification to a user.
     */
    public function sendNotification(Request $request)
{
    // Ensure the user is authenticated
    $user = $request->user();
        // Ensure the user is authenticated
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        // Check if the authenticated user is an admin
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

    // Validate input
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id', // Ensure the user_id exists in the users table
        'title' => 'required|string|max:255',    // Title should be a string with a max length of 255
        'message' => 'required|string',          // Message is required and should be a string
        'status' => 'required|string|in:unread,read', // Ensure the status is either 'unread' or 'read'
    ]);

    // If validation fails, return the validation errors
    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    // Check if the user exists before creating the notification
    $recipient = \App\Models\User::find($request->user_id);
    if (!$recipient) {
        return response()->json(['error' => 'User not found'], 404);
    }

    // Create the notification
    $notification = Notification::create([
        'user_id' => $request->user_id,
        'title' => $request->title,
        'message' => $request->message,
        'status' => $request->status,
    ]);

    // Return a success response with the created notification
    return response()->json([
        'message' => 'Notification sent successfully',
        'notification' => $notification
    ], 201);
}


    /**
     * Get all notifications for the authenticated user.
     */
    public function getNotifications(Request $request)
{
    $user = $request->user(); // Retrieve the authenticated user

    if (!$user) {
        return response()->json(['error' => 'Unauthorized'], 401); // If no user is authenticated
    }

    // Debug: Check if user data is properly retrieved
    // dd($user); // You can use dd() to inspect the user data for debugging.

    // Check if the role exists and if it's properly assigned
    if ($user->role === 'admin') {
        // Admin can view all notifications
        $notifications = Notification::orderBy('created_at', 'desc')->get();
    } else {
        // Regular user can only view their own notifications
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc') // Ensure sorting by timestamp
            ->get();
    }

    return response()->json(['notifications' => $notifications], 200);
}

public function deleteNotification(Request $request, $id)
{
    $user = $request->user();

    // Check if the user is an admin or if the notification belongs to the user
    $notification = Notification::find($id);

    if (!$notification) {
        return response()->json(['error' => 'Notification not found'], 404);
    }

    // Admins can delete any notification, regular users can only delete their own notifications
    if ($user->role !== 'admin' && $notification->user_id !== $user->id) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // Delete the notification
    $notification->delete();

    return response()->json(['message' => 'Notification deleted successfully'], 200);
}
}
