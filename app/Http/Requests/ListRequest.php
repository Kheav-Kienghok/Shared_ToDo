<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListRequest extends FormRequest
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
            "name" => ["sometimes", "required", "string", "max:255"],
            "description" => ["sometimes", "nullable", "string"],
            "is_achieved" => ["sometimes", "boolean"],
        ];
    }

    public function messages(): array
    {
        if ($this->isMethod("post")) {
            return [
                "name" => ["required", "string", "max:255"], // must be present
                "description" => ["nullable", "string"],
                "is_achieved" => ["sometimes", "boolean"],
            ];
        }

        // For update (PATCH/PUT)
        return [
            "name" => ["sometimes", "string", "max:255"], // optional on update
            "description" => ["sometimes", "nullable", "string"],
            "is_achieved" => ["sometimes", "boolean"],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            "is_achieved" => $this->has("is_achieved")
                ? $this->boolean("is_achieved")
                : false,
        ]);
    }
}
