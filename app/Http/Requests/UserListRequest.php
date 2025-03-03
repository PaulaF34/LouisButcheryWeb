<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserListRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Allow everyone or use authorization logic
    }

    public function rules()
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],      // Pagination: page must be integer and >= 1
            'per_page' => ['nullable', 'integer', 'min:1'],   // Pagination: per_page must be integer and >= 1
            'name' => ['nullable', 'string'],                  // Filter by name (optional)
            'role' => ['nullable', 'string', 'in:admin,customer'], // Filter by role (optional)
        ];
    }

    public function messages()
    {
        return [
            'page.integer' => 'Page must be a valid integer.',
            'per_page.integer' => 'Per page must be a valid integer.',
            'role.in' => 'Role must be either admin or customer.',
        ];
    }
}
