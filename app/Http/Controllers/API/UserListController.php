<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\UserListRequest;
use Illuminate\Http\JsonResponse;

class UserListController extends Controller
{
    public function index(UserListRequest $request): JsonResponse
    {
        // Ensure only admin can access this method
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Admins only.'
            ], 403);
        }

        // Get validated input
        $validatedData = $request->validated();

        // Fetch users with optional filters
        $users = User::when($validatedData['name'] ?? null, function ($query, $name) {
                return $query->where('name', 'like', "%{$name}%");
            })
            ->when($validatedData['role'] ?? null, function ($query, $role) {
                return $query->where('role', $role);
            })
            ->paginate($validatedData['per_page'] ?? 10);  // Default to 10 per page

        return response()->json([
            'success' => true,
            'message' => 'User list retrieved successfully.',
            'users' => $users
        ]);
    }
}
