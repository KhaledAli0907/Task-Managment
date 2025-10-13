<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskStoreRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string|in:pending,in_progress,completed,cancelled',
            'completed' => 'boolean',
            'due_date' => 'nullable|date|after:now',
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
            'due_date.after' => 'The due date must be a future date.',
            'assignee_id.exists' => 'The selected assignee does not exist.'
        ];
    }
}
