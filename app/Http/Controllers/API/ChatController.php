<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Chat;

class ChatController extends Controller
{
    // Start a new chat session
    public function startChat(Request $request)
    {
        // Get the authenticated user ID
        $userId = Auth::user()->id;

        // Validate the incoming message
        $request->validate([
            'message' => 'required|string',
        ]);

        // Create a new chat session (first message from user)
        $chat = Chat::create([
            'user_id' => $userId,
            'message' => $request->message,
            'sender' => 'customer', // Set sender as user
        ]);

        return response()->json([
            'message' => 'Message sent successfully.',
            'chatId' => $chat->id, // Return the newly created chat ID
        ], 201);
    }

    // Get chat history for a specific chatId
    public function getChatHistory($chatId)
{
    // Retrieve the messages for the given chatId (assuming the column is 'id')
    $messages = Chat::where('id', $chatId)
                    ->orderBy('created_at', 'asc')
                    ->get();

    return response()->json($messages, 200);
}
}
