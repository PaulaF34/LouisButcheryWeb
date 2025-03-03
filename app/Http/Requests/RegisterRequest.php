<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^[0-9]{6,20}$/'], // Allows 6 to 20 digits
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*?&]{8,}$/'],

        ];
    }

    public function messages()
{
    return [
        'name.required' => 'Please enter your name.',
        'email.required' => 'Email is required.',
        'email.unique' => 'This email is already taken.',
        'phone.regex' => 'Phone number must be 6 to 20 digits.',
        'password.regex' => 'Password must contain at least one letter and one number.',
    ];
}

}
