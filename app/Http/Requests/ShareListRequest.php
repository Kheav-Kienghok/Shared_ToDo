<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShareListRequest extends FormRequest
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
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['required', Rule::in(['collaborator', 'viewer'])],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Please provide a user ID to share with.',
            'user_id.exists' => 'The selected user does not exist.',
            'role.required' => 'Please provide a role (collaborator/viewer).',
            'role.in' => 'The role must be either collaborator or viewer.',
        ];
    }
}
