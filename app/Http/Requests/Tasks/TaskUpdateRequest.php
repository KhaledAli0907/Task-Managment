<?php

namespace App\Http\Requests\Tasks;

use Illuminate\Foundation\Http\FormRequest;

class TaskUpdateRequest extends FormRequest
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
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|string|in:pending,in_progress,completed,cancelled',
            'completed' => 'boolean',
            'due_date' => 'nullable|date',
            'assignee_id' => 'nullable|exists:users,id'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The task title is required.',
            'title.max' => 'The task title may not be greater than 255 characters.',
            'status.required' => 'The task status is required.',
            'status.in' => 'The task status must be one of: pending, in_progress, completed, cancelled.',
            'due_date.date' => 'The due date must be a valid date.',
            'assignee_id.exists' => 'The selected assignee does not exist.'
        ];
    }
}
