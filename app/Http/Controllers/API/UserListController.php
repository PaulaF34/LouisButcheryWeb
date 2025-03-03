<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\UserListRequest;

class UserListController extends Controller
{
    public function index(UserListRequest $request)
{
    // Get validated input
    $validatedData = $request->validated();

    // Example: Applying pagination with optional filters
    $users = User::when($validatedData['name'] ?? null, function ($query, $name) {
        return $query->where('name', 'like', "%{$name}%");
    })
    ->when($validatedData['role'] ?? null, function ($query, $role) {
        return $query->where('role', $role);
    })
    ->paginate($validatedData['per_page'] ?? 10);  // Default to 10 per page

    return response()->json($users);
}

}
