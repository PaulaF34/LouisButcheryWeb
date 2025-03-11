<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderListRequest extends FormRequest
{
    public function rules()
    {
        return [
            'status' => 'nullable|string',
            'user_name' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:100',  // Optional pagination size
        ];
    }

    public function authorize()
    {
        return true; // Allow this request for now; you can add authorization checks as needed.
    }
}
