<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('user')?->id;

        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId),
            ],
            'password' => ['nullable', 'string', 'min:6', 'max:255'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'      => "Please enter the user's name",
            'name.string'        => "The user's name must be valid text",
            'name.max'           => "The user's name must not exceed 255 characters",

            'email.required'     => "Please enter the user's email address",
            'email.email'        => 'Please enter a valid email address',
            'email.max'          => 'The email address must not exceed 255 characters',
            'email.unique'       => 'This email address is already registered',

            'password.string'    => 'The password must be valid text',
            'password.min'       => 'The password must be at least 6 characters',
            'password.max'       => 'The password must not exceed 255 characters',
        ];
    }
}
