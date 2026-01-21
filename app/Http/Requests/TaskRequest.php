<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
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
        if ($this->isMethod('post')) {
            return [
                'title' => 'required|string|max:255',
                'status' => 'required|in:pending,in_progress,completed',
                'priority' => 'required|integer|min:0|max:5',
                'description' => 'nullable|string',
                'assigned_to' => 'nullable|integer|exists:users,id',
                'due_date' => 'nullable|date',
                'completed_at' => 'nullable|date',
                'reminder_at' => 'nullable|date',
            ];
        }

        // PUT / PATCH (update)
        return [
            'title' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:pending,in_progress,completed',
            'priority' => 'sometimes|integer|min:0|max:5',
            'description' => 'sometimes|nullable|string',
            'due_date' => 'sometimes|nullable|date',
            'completed_at' => 'sometimes|nullable|date',
            'reminder_at' => 'sometimes|nullable|date',
        ];
    }


    public function messages(): array
    {
        return [
            'title.required' => 'The task title is required.',
            'title.string' => 'The task title must be a string.',
            'title.max' => 'The task title may not be greater than 255 characters.',
            'description.string' => 'The task description must be a string.',
            'assigned_to.integer' => 'The assigned to field must be an integer.',
            'assigned_to.exists' => 'The selected user does not exist.',
            'status.required' => 'The task status is required.',
            'status.string' => 'The task status must be a string.',
            'status.in' => 'The task status must be one of the following: pending, in_progress, completed.',
            'priority.required' => 'The task priority is required.',
            'priority.integer' => 'The task priority must be an integer.',
            'priority.min' => 'The task priority must be at least 0.',
            'priority.max' => 'The task priority may not be greater than 5.',
            'due_date.date' => 'The due date is not a valid date.',
            'completed_at.date' => 'The completed at is not a valid date.',
            'reminder_at.date' => 'The reminder at is not a valid date.',
        ];
    }

    /**
     * This runs BEFORE validation rules.
     * Perfect for auto-filling or normalizing input.
     */
    protected function prepareForValidation(): void
    {
        if ($this->status === 'completed' && !$this->completed_at) {
            $this->merge([
                'completed_at' => now(),
            ]);
        }
    }
}
