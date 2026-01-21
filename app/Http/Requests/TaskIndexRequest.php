<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskIndexRequest extends FormRequest
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
            'status' => 'nullable|in:pending,in_progress,completed',
            'due' => 'nullable|in:today',
            'overdue' => 'nullable|boolean',
        ];
    }

    public function passedValidation()
    {
        // Convert overdue to boolean
        if ($this->has('overdue')) {
            $this->merge(['overdue' => filter_var($this->overdue, FILTER_VALIDATE_BOOLEAN)]);
        }
    }
}
