<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAgentRequest extends FormRequest
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
        $agentId = $this->route('agent')?->id;

        return [
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('agents')->ignore($agentId)],
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
            'name.required'  => 'Please enter the agent name',
            'name.string'    => 'The agent name must be valid text',
            'name.max'       => 'The agent name cannot be longer than 255 characters',

            'email.required' => 'Please enter the agent email',
            'email.email'    => 'Please enter a valid email address',
            'email.max'      => 'The email cannot be longer than 255 characters',
            'email.unique'   => 'This email is already registered',
        ];
    }
}
